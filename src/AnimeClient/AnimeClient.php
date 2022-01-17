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

namespace Aviat\AnimeClient;

use Aviat\Ion\ImageBuilder;
use Psr\SimpleCache\CacheInterface;

use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Amp\Http\Client\HttpClient;
use Amp\Http\Client\HttpClientBuilder;

use Aviat\Ion\ConfigInterface;
use Yosymfony\Toml\{Toml, TomlBuilder};

use Throwable;

use function Amp\Promise\wait;
use function Aviat\Ion\_dir;
// ----------------------------------------------------------------------------
//! TOML Functions
// ----------------------------------------------------------------------------

/**
 * Load configuration options from .toml files
 *
 * @codeCoverageIgnore
 * @param string $path - Path to load config
 * @return array
 */
function loadConfig(string $path): array
{
	$output = [];
	$files = glob("{$path}/*.toml");

	if ( ! is_array($files))
	{
		return [];
	}

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
 * @codeCoverageIgnore
 * @param string $filename
 * @return array
 */
function loadTomlFile(string $filename): array
{
	return Toml::parseFile($filename);
}

function _iterateToml(TomlBuilder $builder, iterable $data, mixed $parentKey = NULL): void
{
	foreach ($data as $key => $value)
	{
		// Skip unsupported empty value
		if ($value === NULL)
		{
			continue;
		}


		if (is_scalar($value) || isSequentialArray($value))
		{
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
 * @param iterable $data
 * @return string
 */
function arrayToToml(iterable $data): string
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

// ----------------------------------------------------------------------------
//! Misc Functions
// ----------------------------------------------------------------------------

if ( ! function_exists('array_is_list'))
{
	/**
	 * Polyfill for PHP 8
	 *
	 * @see https://www.php.net/manual/en/function.array-is-list
	 * @param array $a
	 * @return bool
	 */
	function array_is_list(array $a): bool
	{
		return $a === [] || (array_keys($a) === range(0, count($a) - 1));
	}
}

/**
 * Is the array sequential, not associative?
 *
 * @param mixed $array
 * @return bool
 */
function isSequentialArray(mixed $array): bool
{
	if ( ! is_array($array))
	{
		return FALSE;
	}

	return array_is_list($array);
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

	$APP_DIR = _dir($config->get('root'), 'app');

	$pathMap = [
		'app/config' => "{$APP_DIR}/config",
		'app/logs' => "{$APP_DIR}/logs",
		'public/images/anime' => "{$publicDir}/images/anime",
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
			// @codeCoverageIgnoreStart
			$errors['writable'][] = $pretty;
			// @codeCoverageIgnoreEnd
		}
	}

	return $errors;
}

/**
 * Get an API Client, with better defaults
 *
 * @return HttpClient
 */
function getApiClient (): HttpClient
{
	static $client;

	if ($client === NULL)
	{
		$client = HttpClientBuilder::buildDefault();
	}

	return $client;
}

/**
 * Simplify making a request with Http\Client
 *
 * @param string|Request $request
 * @return Response
 * @throws Throwable
 */
function getResponse (Request|string $request): Response
{
	$client = getApiClient();

	if (is_string($request))
	{
		$request = new Request($request);
	}

	return wait($client->request($request));
}

/**
 * Generate the path for the cached image from the original image
 *
 * @param string $kitsuUrl
 * @param bool $webp
 * @return string
 */
function getLocalImg (string $kitsuUrl, bool $webp = TRUE): string
{
	if (empty($kitsuUrl) || ( ! is_string($kitsuUrl)))
	{
		return 'images/placeholder.webp';
	}

	$parts = parse_url($kitsuUrl);

	if ($parts === FALSE || ! array_key_exists('path', $parts))
	{
		return 'images/placeholder.webp';
	}

	$file = basename($parts['path']);
	$fileParts = explode('.', $file);
	$ext = array_pop($fileParts);
	$ext = $webp ? 'webp' : $ext;

	$segments = explode('/', trim($parts['path'], '/'));

	$type = $segments[0] === 'users' ? $segments[1] : $segments[0];

	$id = $segments[count($segments) - 2];

	return implode('/', ['images', $type, "{$id}.{$ext}"]);
}

/**
 * Create a transparent placeholder image
 *
 * @codeCoverageIgnore
 * @param string $path
 * @param int $width
 * @param int $height
 * @param string $text
 * @return bool
 */
function createPlaceholderImage (string $path, int $width = 200, int $height = 200, string $text = 'Image Unavailable'): bool
{
	$img = ImageBuilder::new($width, $height)
		->enableAlphaBlending(TRUE)
		->addBackgroundColor(255, 255, 255)
		->addCenteredText($text, 64, 64, 64);

	$path = rtrim($path, '/');

	$savedPng = $img->savePng($path . '/placeholder.png');
	$savedWebp = $img->saveWebp($path . '/placeholder.webp');

	$img->cleanup();

	return $savedPng && $savedWebp;
}

/**
 * Check that there is a value for at least one item in a collection with the specified key
 *
 * @param array $search
 * @param string $key
 * @return bool
 */
function colNotEmpty(array $search, string $key): bool
{
	$items = array_filter(array_column($search, $key), static fn ($x) => ( ! empty($x)));
	return count($items) > 0;
}

/**
 * Clear the cache, but save user auth data
 *
 * @param CacheInterface $cache
 * @return bool
 */
function clearCache(CacheInterface $cache): bool
{
	// Save the user data, if it exists, for priming the cache
	$userData = $cache->getMultiple([
		Kitsu::AUTH_USER_ID_KEY,
		Kitsu::AUTH_TOKEN_CACHE_KEY,
		Kitsu::AUTH_TOKEN_EXP_CACHE_KEY,
		Kitsu::AUTH_TOKEN_REFRESH_CACHE_KEY,
	]);

	$userData = array_filter((array)$userData, static fn ($value) => $value !== NULL);
	$cleared = $cache->clear();

	$saved = ( ! empty($userData)) ? $cache->setMultiple($userData) : TRUE;

	return $cleared && $saved;
}

/**
 * Render a PHP code template as a string
 *
 * @codeCoverageIgnore
 * @param string $path
 * @param array $data
 * @return string
 */
function renderTemplate(string $path, array $data): string
{
	ob_start();
	extract($data, EXTR_OVERWRITE);
	include $path;
	$rawOutput = ob_get_clean();
	return (is_string($rawOutput)) ? $rawOutput : '';
}