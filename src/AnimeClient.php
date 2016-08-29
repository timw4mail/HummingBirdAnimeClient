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

define('SRC_DIR', realpath(__DIR__));

/**
 * Application constants
 */
class AnimeClient {

	const HUMMINGBIRD_AUTH_URL = 'https://hummingbird.me/api/v1/users/authenticate';
	const SESSION_SEGMENT = 'Aviat\AnimeClient\Auth';
	const DEFAULT_CONTROLLER_NAMESPACE = 'Aviat\AnimeClient\Controller';
	const DEFAULT_CONTROLLER = 'Aviat\AnimeClient\Controller\Anime';
	const DEFAULT_CONTROLLER_METHOD = 'index';
	const NOT_FOUND_METHOD = 'not_found';
	const ERROR_MESSAGE_METHOD = 'error_page';
	const SRC_DIR = SRC_DIR;

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