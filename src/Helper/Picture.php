<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.1
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2018  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.1
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Helper;

use Aviat\Ion\Di\ContainerAware;

/**
 * Simplify picture elements
 */
final class Picture {

	use ContainerAware;

	/**
	 * Create the html f
	 *
	 * @param string $webp
	 * @param string $fallbackExt
	 * @param array $picAttrs
	 * @param array $imgAttrs
	 * @return string
	 */
	public function __invoke(string $webp, string $fallbackExt = 'jpg', $picAttrs = [], $imgAttrs = []): string
	{
		$urlGenerator = $this->container->get('url-generator');
		$helper = $this->container->get('html-helper');
		
		// If it is a placeholder image, make the
		// fallback a png, not a jpg
		if (strpos($webp, 'placeholder') !== FALSE)
		{
			$fallbackExt = 'png';
		}

		if (strpos($webp, '//') === FALSE)
		{
			$webp = $urlGenerator->assetUrl($webp);
		}
		

		$urlParts = explode('.', $webp);
		$ext = array_pop($urlParts);
		$path = implode('.', $urlParts);

		$mime = $ext === 'jpg'
			? 'image/jpeg'
			: "image/{$ext}";
		$fallbackMime = $fallbackExt === 'jpg'
			? 'image/jpeg'
			: "image/{$fallbackExt}";

		$fallbackImg = "{$path}.{$fallbackExt}";

		$pictureChildren = [
			$helper->void('source', [
				'srcset' => $webp,
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