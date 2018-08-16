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

use Aviat\Ion\ConfigInterface;
use Yosymfony\Toml\Toml;

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
		$config = Toml::parseFile($file);

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

/**
 * Check that folder permissions are correct for proper operation
 *
 * @param ConfigInterface $config
 * @return array
 */
function checkFolderPermissions(ConfigInterface $config): array
{
	$errors = [];
	$publicDir = $config->get('asset_dir');

	$pathMap = [
		'app/logs' => realpath(__DIR__ . '/../app/logs'),
		'public/js/cache' => "{$publicDir}/js/cache",
		'public/images/avatars' => "{$publicDir}/images/avatars",
		'public/images/anime' => "{$publicDir}/images/anime",
		'public/images/characters' => "{$publicDir}/images/characters",
		'public/images/manga' => "{$publicDir}/images/manga",
		'public/images/people' => "{$publicDir}/images/people",
	];

	foreach ($pathMap as $pretty => $actual)
	{
		// Make sure the folder exists first
		if ( ! is_dir($actual))
		{
			$errors['missing'][] = $pretty;
			continue;
		}

		$writable = is_writable($actual) && is_executable($actual);

		if ( ! $writable)
		{
			$errors['writable'][] = $pretty;
		}
	}

	return $errors;
}