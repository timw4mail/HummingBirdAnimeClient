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

namespace Aviat\AnimeClient;

use Aviat\Ion\ConfigInterface;
use Yosymfony\Toml\{Toml, TomlBuilder};

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
		if ($key === 'admin-override')
		{
			continue;
		}

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
 * Load config from one specific TOML file
 *
 * @param string $filename
 * @return array
 */
function loadTomlFile(string $filename): array
{
	return Toml::parseFile($filename);
}

/**
 * Is the array sequential, not associative?
 *
 * @param mixed $array
 * @return bool
 */
function isSequentialArray($array): bool
{
	if ( ! is_array($array))
	{
		return FALSE;
	}

	$i = 0;
	foreach ($array as $k => $v)
	{
		if ($k !== $i++)
		{
			return FALSE;
		}
	}
	return TRUE;
}

function _iterateToml(TomlBuilder $builder, $data, $parentKey = NULL): void
{
	foreach ($data as $key => $value)
	{
		if ($value === NULL)
		{
			continue;
		}


		if (is_scalar($value) || isSequentialArray($value))
		{
			// $builder->addTable('');
			$builder->addValue($key, $value);
			continue;
		}

		$newKey = ($parentKey !== NULL)
			? "{$parentKey}.{$key}"
			: $key;

		if ( ! isSequentialArray($value))
		{
			$builder->addTable($newKey);
		}

		_iterateToml($builder, $value, $newKey);
	}
}

/**
 * Serialize config data into a Toml file
 *
 * @param mixed $data
 * @return string
 */
function arrayToToml($data): string
{
	$builder = new TomlBuilder();

	_iterateToml($builder, $data);

	return $builder->getTomlString();
}

/**
 * Serialize toml back to an array
 *
 * @param string $toml
 * @return array
 */
function tomlToArray(string $toml): array
{
	return Toml::parse($toml);
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
		'app/config' => realpath(__DIR__ . '/../app/config'),
		'app/logs' => realpath(__DIR__ . '/../app/logs'),
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