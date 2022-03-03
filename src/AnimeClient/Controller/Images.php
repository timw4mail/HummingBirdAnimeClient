<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2021  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Controller;

use Aviat\AnimeClient\Controller as BaseController;
use Throwable;
use function Amp\Promise\wait;
use function Aviat\AnimeClient\{createPlaceholderImage, getResponse};
use function imagepalletetotruecolor;

use function in_array;

/**
 * Controller for handling routes that don't fit elsewhere
 */
final class Images extends BaseController
{
	/**
	 * Get image covers from kitsu
	 *
	 * @param string $type The category of image
	 * @param string $file The filename to look for
	 * @param bool $display Whether to output the image to the server
	 * @throws Throwable
	 */
	public function cache(string $type, string $file, bool $display = TRUE): void
	{
		$currentUrl = (string) $this->request->getUri();

		$kitsuUrl = 'https://media.kitsu.io/';
		$fileName = str_replace('-original', '', $file);
		[$id, $ext] = explode('.', basename($fileName));

		$baseSavePath = $this->config->get('img_cache_path');

		// Kitsu doesn't serve webp, but for most use cases,
		// jpg is a safe assumption
		$tryJpg = ['anime', 'characters', 'manga', 'people'];
		if ($ext === 'webp' && in_array($type, $tryJpg, TRUE))
		{
			$ext = 'jpg';
			$currentUrl = str_replace('webp', 'jpg', $currentUrl);
		}

		$typeMap = [
			'anime' => [
				'kitsuUrl' => "anime/poster_images/{$id}/medium.{$ext}",
				'width' => 220,
				'height' => 312,
			],
			'avatars' => [
				'kitsuUrl' => "users/avatars/{$id}/original.{$ext}",
				'width' => NULL,
				'height' => NULL,
			],
			'characters' => [
				'kitsuUrl' => "characters/images/{$id}/original.{$ext}",
				'width' => 225,
				'height' => 350,
			],
			'manga' => [
				'kitsuUrl' => "manga/poster_images/{$id}/medium.{$ext}",
				'width' => 220,
				'height' => 312,
			],
			'people' => [
				'kitsuUrl' => "people/images/{$id}/original.{$ext}",
				'width' => NULL,
				'height' => NULL,
			],
		];

		$imageType = $typeMap[$type] ?? NULL;

		if (NULL === $imageType)
		{
			$this->getPlaceholder($baseSavePath, 200, 200);

			return;
		}

		$kitsuUrl .= $imageType['kitsuUrl'];
		$width = $imageType['width'];
		$height = $imageType['height'];
		$filePrefix = "{$baseSavePath}/{$type}/{$id}";

		$response = getResponse($kitsuUrl);

		if ($response->getStatus() !== 200)
		{
			// Try a few different file types before giving up
			// webm => jpg => png => gif
			$nextType = [
				'jpg' => 'png',
				'png' => 'gif',
			];

			if (array_key_exists($ext, $nextType))
			{
				$newUrl = str_replace($ext, $nextType[$ext], $currentUrl);
				$this->redirect($newUrl, 303);

				return;
			}

			if ($display)
			{
				$this->getPlaceholder("{$baseSavePath}/{$type}", $width, $height);
			}
			else
			{
				createPlaceholderImage("{$baseSavePath}/{$type}", $width, $height);
			}

			return;
		}

		$data = wait($response->getBody()->buffer());

		[$origWidth] = getimagesizefromstring($data);
		$gdImg = imagecreatefromstring($data);
		if ($gdImg === FALSE)
		{
			return;
		}

		$resizedImg = imagescale($gdImg, $width ?? $origWidth);
		if ($resizedImg === FALSE)
		{
			return;
		}

		if ($ext === 'gif')
		{
			file_put_contents("{$filePrefix}.gif", $data);
			imagepalletetotruecolor($gdImg);
		}

		// save the webp versions
		imagewebp($gdImg, "{$filePrefix}-original.webp");
		imagewebp($resizedImg, "{$filePrefix}.webp");

		// save the scaled jpeg file
		imagejpeg($resizedImg, "{$filePrefix}.jpg");

		// And the original
		file_put_contents("{$filePrefix}-original.jpg", $data);

		imagedestroy($gdImg);
		imagedestroy($resizedImg);

		if ($display)
		{
			$contentType = ($ext === 'webp')
				? 'image/webp'
				: $response->getHeader('content-type')[0] ?? 'image/jpeg';

			$outputFile = (str_contains($file, '-original'))
				? "{$filePrefix}-original.{$ext}"
				: "{$filePrefix}.{$ext}";

			header("Content-Type: {$contentType}");
			echo file_get_contents($outputFile);
		}
	}

	/**
	 * Get a placeholder for a missing image
	 */
	private function getPlaceholder(string $path, ?int $width = 200, ?int $height = NULL): void
	{
		$height ??= $width;

		$filename = $path . '/placeholder.png';

		if ( ! file_exists($path . '/placeholder.png'))
		{
			createPlaceholderImage($path, $width, $height);
		}

		header('Content-Type: image/png');
		echo file_get_contents($filename);
	}
}
