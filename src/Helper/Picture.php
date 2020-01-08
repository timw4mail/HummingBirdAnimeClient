<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.2
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Helper;

use Aviat\Ion\Di\ContainerAware;

/**
 * Simplify picture elements
 */
final class Picture {

	use ContainerAware;

	private const MIME_MAP = [
		'apng' => 'image/vnd.mozilla.apng',
		'bmp' => 'image/bmp',
		'gif' => 'image/gif',
		'ico' => 'image/x-icon',
		'jpeg' => 'image/jpeg',
		'jpf' => 'image/jpx',
		'jpg' => 'image/jpeg',
		'jpx' => 'image/jpx',
		'png' => 'image/png',
		'svg' => 'image/svg+xml',
		'tif' => 'image/tiff',
		'tiff' => 'image/tiff',
		'webp' => 'image/webp',
	];

	private const SIMPLE_IMAGE_TYPES = [
		'gif',
		'jpeg',
		'jpg',
		'png',
	];

	/**
	 * Create the html for an html picture element
	 *
	 * @param string $uri
	 * @param string $fallbackExt
	 * @param array $picAttrs
	 * @param array $imgAttrs
	 * @return string
	 */
	public function __invoke(string $uri, string $fallbackExt = 'jpg', array $picAttrs = [], array $imgAttrs = []): string
	{
		$urlGenerator = $this->container->get('url-generator');
		$helper = $this->container->get('html-helper');

		// If it is a placeholder image, make the
		// fallback a png, not a jpg
		if (strpos($uri, 'placeholder') !== FALSE)
		{
			$fallbackExt = 'png';
		}

		if (strpos($uri, '//') === FALSE)
		{
			$uri = $urlGenerator->assetUrl($uri);
		}

		$urlParts = explode('.', $uri);
		$ext = array_pop($urlParts);
		$path = implode('.', $urlParts);

		$mime = array_key_exists($ext, static::MIME_MAP)
			? static::MIME_MAP[$ext]
			: 'image/jpeg';

		$fallbackMime = array_key_exists($fallbackExt, static::MIME_MAP)
			? static::MIME_MAP[$fallbackExt]
			: 'image/jpeg';

		// For image types that are well-established, just return a
		// simple <img /> element instead
		if (
			$ext === $fallbackExt ||
			\in_array($ext, static::SIMPLE_IMAGE_TYPES, TRUE)
		)
		{
			$attrs = ( ! empty($imgAttrs))
				? $imgAttrs
				: $picAttrs;

			return $helper->img($uri, $attrs);
		}

		$fallbackImg = "{$path}.{$fallbackExt}";

		$pictureChildren = [
			$helper->void('source', [
				'srcset' => $uri,
				'type' => $mime,
			]),
			$helper->void('source', [
				'srcset' => $fallbackImg,
				'type' => $fallbackMime
			]),
			$helper->img($fallbackImg, array_merge(['alt' => ''], $imgAttrs)),
		];

		$sources = implode('', $pictureChildren);

		return $helper->elementRaw('picture', $sources, $picAttrs);
	}
}
// End of Picture.php