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

namespace Aviat\AnimeClient\Helper;

use Aviat\Ion\Di\ContainerAware;

/**
 * Simplify picture elements
 */
final class Picture {

	use ContainerAware;

	private const SIMPLE_IMAGE_TYPES = [
		'gif',
		'jpeg',
		'jpg',
		'png',
	];

	/**
	 * Create the html for an html picture element.
	 * Uses .webp images with fallback
	 */
	public function __invoke(string $uri, string $fallbackExt = 'jpg', array $picAttrs = [], array $imgAttrs = []): string
	{
		$urlGenerator = $this->container->get('url-generator');
		$helper = $this->container->get('html-helper');

		$imgAttrs['loading'] = 'lazy';
		$picAttrs['loading'] = 'lazy';

		// If it is a placeholder image, make the
		// fallback a png, not a jpg
		if (str_contains($uri, 'placeholder'))
		{
			$fallbackExt = 'png';
		}

		if ( ! str_contains($uri, '//'))
		{
			$uri = $urlGenerator->assetUrl($uri);
		}

		$urlParts = explode('.', $uri);
		$ext = array_pop($urlParts);
		$path = implode('.', $urlParts);

		$mime = match ($ext) {
			'avif' => 'image/avif',
			'apng' => 'image/vnd.mozilla.apng',
			'bmp' => 'image/bmp',
			'gif' => 'image/gif',
			'ico' => 'image/x-icon',
			'jpf', 'jpx' => 'image/jpx',
			'png' => 'image/png',
			'svg' => 'image/svg+xml',
			'tif', 'tiff' => 'image/tiff',
			'webp' => 'image/webp',
			default => 'image/jpeg',
		};

		$fallbackMime = match ($fallbackExt) {
			'gif' => 'image/gif',
			'png' => 'image/png',
			default => 'image/jpeg',
		};

		// For image types that are well-established, just return a
		// simple <img /> element instead
		if (
			$ext === $fallbackExt ||
			\in_array($ext, Picture::SIMPLE_IMAGE_TYPES, TRUE)
		)
		{
			$attrs = (count($imgAttrs) > 1)
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