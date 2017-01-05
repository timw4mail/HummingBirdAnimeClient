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

use Yosymfony\Toml\Toml;

define('SRC_DIR', realpath(__DIR__));

/**
 * Application constants
 */
class AnimeClient {

	const KITSU_AUTH_URL = 'https://kitsu.io/api/oauth/token';
	const SESSION_SEGMENT = 'Aviat\AnimeClient\Auth';
	const DEFAULT_CONTROLLER_NAMESPACE = 'Aviat\AnimeClient\Controller';
	const DEFAULT_CONTROLLER = 'Aviat\AnimeClient\Controller\Anime';
	const DEFAULT_CONTROLLER_METHOD = 'index';
	const NOT_FOUND_METHOD = 'notFound';
	const ERROR_MESSAGE_METHOD = 'errorPage';
	const SRC_DIR = SRC_DIR;

	/**
	 * Load configuration options from .toml files
	 *
	 * @param string $path - Path to load config
	 * @return array
	 */
	public static function loadToml(string $path): array
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