<?php
/**
 * Hummingbird Anime Client
 *
 * An API client for Hummingbird to manage anime and manga watch lists
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren
 * @copyright   Copyright (c) 2015 - 2016
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 * @license     MIT
 */

namespace Aviat\AnimeClient;

use Yosymfony\Toml\Toml;

define('SRC_DIR', realpath(__DIR__ . '/../../'));

/**
 * Odds and Ends class
 */
class AnimeClient {

	use \Aviat\Ion\Di\ContainerAware;

	const SESSION_SEGMENT = 'Aviat\AnimeClient\Auth';
	const DEFAULT_CONTROLLER_NAMESPACE = 'Aviat\AnimeClient\Controller';
	const DEFAULT_CONTROLLER = 'Aviat\AnimeClient\Controller\Anime';
	const DEFAULT_CONTROLLER_METHOD = 'index';
	const NOT_FOUND_METHOD = 'not_found';
	const ERROR_MESSAGE_METHOD = 'error_page';
	const SRC_DIR = SRC_DIR;

	private static $form_pages = [
		'edit',
		'add',
		'update',
		'update_form',
		'login',
		'logout'
	];

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
		$page_segments = explode("/", $url);

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
	 * Load configuration options from .toml files
	 *
	 * @param string $path - Path to load config
	 * @return array
	 */
	public static function load_toml($path)
	{
		$output = [];
		$files = glob("{$path}/*.toml");

		foreach ($files as $file)
		{
			$key = str_replace('.toml', '', basename($file));
			$toml = file_get_contents($file);
			$config = Toml::Parse($toml);

			if ($key === 'config')
			{
				foreach($config as $name => $value)
				{
					$output[$name] = $value;
				}

				continue;
			}

			$output[$key] = $config;
		}

		return $output;
	}
}
// End of AnimeClient.php