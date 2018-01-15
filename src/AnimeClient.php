<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2018  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient;

use Yosymfony\Toml\Toml;

if ( ! defined('SRC_DIR'))
{
	\define('SRC_DIR', \realpath(__DIR__));
}

const SESSION_SEGMENT = 'Aviat\AnimeClient\Auth';
const DEFAULT_CONTROLLER = 'Aviat\AnimeClient\Controller\Index';
const DEFAULT_CONTROLLER_NAMESPACE = 'Aviat\AnimeClient\Controller';
const DEFAULT_LIST_CONTROLLER = 'Aviat\AnimeClient\Controller\Anime';
const DEFAULT_CONTROLLER_METHOD = 'index';
const NOT_FOUND_METHOD = 'notFound';
const ERROR_MESSAGE_METHOD = 'errorPage';
const SRC_DIR = SRC_DIR;


if ( ! \function_exists('Aviat\AnimeClient\loadToml'))
{
	/**
	 * Load configuration options from .toml files
	 *
	 * @param string $path - Path to load config
	 * @return array
	 */
	function loadToml(string $path): array
	{
		$output = [];
		$files = glob("{$path}/*.toml");

		foreach ($files as $file)
		{
			$key = str_replace('.toml', '', basename($file));
			$toml = file_get_contents($file);
			$config = Toml::parse($toml);

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
