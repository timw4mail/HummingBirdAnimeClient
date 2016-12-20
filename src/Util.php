<?php declare(strict_types=1);
/**
 * Hummingbird Anime Client
 *
 * An API client for Hummingbird to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2016  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     3.1
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient;

use abeautifulsite\SimpleImage;
use Aviat\Ion\ConfigInterface;
use Aviat\Ion\Di\{ContainerAware, ContainerInterface};
use DomainException;

/**
 * Utility method class
 */
class Util {

	use ContainerAware;

	/**
	 * Routes that don't require a second navigation level
	 * @var array
	 */
	private static $form_pages = [
		'edit',
		'add',
		'update',
		'update_form',
		'login',
		'logout',
		'details'
	];

	/**
	 * The config manager
	 * @var ConfigInterface
	 */
	private $config;

	/**
	 * Set up the Util class
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);
		$this->config = $container->get('config');
	}

	/**
	 * HTML selection helper function
	 *
	 * @param string $a - First item to compare
	 * @param string $b - Second item to compare
	 * @return string
	 */
	public static function is_selected($a, $b)
	{
		return ($a === $b) ? 'selected' : '';
	}

	/**
	 * Inverse of selected helper function
	 *
	 * @param string $a - First item to compare
	 * @param string $b - Second item to compare
	 * @return string
	 */
	public static function is_not_selected($a, $b)
	{
		return ($a !== $b) ? 'selected' : '';
	}

	/**
	 * Determine whether to show the sub-menu
	 *
	 * @return bool
	 */
	public function is_view_page()
	{
		$url = $this->container->get('request')
			->getUri();
		$page_segments = explode("/", (string) $url);

		$intersect = array_intersect($page_segments, self::$form_pages);

		return empty($intersect);
	}

	/**
	 * Determine whether the page is a page with a form, and
	 * not suitable for redirection
	 *
	 * @return boolean
	 */
	public function is_form_page()
	{
		return ! $this->is_view_page();
	}

	/**
	 * Get the path of the cached version of the image. Create the cached image
	 * if the file does not already exist
	 *
	 * @codeCoverageIgnore
	 * @param string $api_path - The original image url
	 * @param string $series_slug - The part of the url with the series name, becomes the image name
	 * @param string $type - Anime or Manga, controls cache path
	 * @return string - the frontend path for the cached image
	 * @throws \DomainException
	 */
	public function get_cached_image($api_path, $series_slug, $type = "anime")
	{
		$path_parts = explode('?', basename($api_path));
		$path = current($path_parts);
		$ext_parts = explode('.', $path);
		$ext = end($ext_parts);

		// Workaround for some broken file extensions
		if ($ext === "jjpg")
		{
			$ext = "jpg";
		}

		// Failsafe for weird urls
		if (strlen($ext) > 3)
		{
			return $api_path;
		}

		$img_cache_path = $this->config->get('img_cache_path');
		$cached_image = "{$series_slug}.{$ext}";
		$cached_path = "{$img_cache_path}/{$type}/{$cached_image}";

		// Cache the file if it doesn't already exist
		if ( ! file_exists($cached_path))
		{
			if (function_exists('curl_init'))
			{
				$ch = curl_init($api_path);
				$fp = fopen($cached_path, 'wb');
				curl_setopt_array($ch, [
					CURLOPT_FILE => $fp,
					CURLOPT_HEADER => 0
				]);
				curl_exec($ch);
				curl_close($ch);
				fclose($fp);
			}
			else if (ini_get('allow_url_fopen'))
			{
				copy($api_path, $cached_path);
			}
			else
			{
				throw new DomainException("Couldn't cache images because they couldn't be downloaded.");
			}

			// Resize the image
			if ($type === 'anime')
			{
				$resize_width = 220;
				$resize_height = 319;
				$this->_resize($cached_path, $resize_width, $resize_height);
			}
		}

		return "/public/images/{$type}/{$cached_image}";
	}

	/**
	 * Resize an image
	 *
	 * @codeCoverageIgnore
	 * @param string $path
	 * @param string $width
	 * @param string $height
	 * @return void
	 */
	private function _resize($path, $width, $height)
	{
		try
		{
			$img = new SimpleImage($path);
			$img->resize($width, $height)->save();
		}
		catch (Exception $e)
		{
			// Catch image errors, since they don't otherwise affect
			// functionality
		}
	}
}