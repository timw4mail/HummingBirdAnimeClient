<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4+
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient;

use Aviat\AnimeClient\Kitsu;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use function Amp\Promise\wait;

use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Amp\Http\Client\HttpClient;
use Amp\Http\Client\HttpClientBuilder;

use Aviat\Ion\ConfigInterface;
use Yosymfony\Toml\{Toml, TomlBuilder};

use Throwable;

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

function _iterateToml(TomlBuilder $builder, iterable $data, $parentKey = NULL): void
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
 * @param mixed $data
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
		'app/config' => realpath(__DIR__ . '/../../app/config'),
		'app/logs' => realpath(__DIR__ . '/../../app/logs'),
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
function getResponse ($request): Response
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
function getLocalImg (string $kitsuUrl, $webp = TRUE): string
{
	if (empty($kitsuUrl) || ( ! is_string($kitsuUrl)))
	{
		return 'images/placeholder.webp';
	}

	$parts = parse_url($kitsuUrl);

	if ($parts === FALSE)
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
 * @param string $path
 * @param int $width
 * @param int $height
 * @param string $text
 */
function createPlaceholderImage ($path, ?int $width, ?int $height, $text = 'Image Unavailable'): void
{
	$width = $width ?? 200;
	$height = $height ?? 200;

	$img = imagecreatetruecolor($width, $height);
	imagealphablending($img, TRUE);

	$path = rtrim($path, '/');

	// Background is the first color by default
	$fillColor = imagecolorallocatealpha($img, 255, 255, 255, 127);
	imagefill($img, 0, 0, $fillColor);

	$textColor = imagecolorallocate($img, 64, 64, 64);

	imagealphablending($img, TRUE);

	// Generate placeholder text
	$fontSize = 10;
	$fontWidth = imagefontwidth($fontSize);
	$fontHeight = imagefontheight($fontSize);
	$length = \strlen($text);
	$textWidth = $length * $fontWidth;
	$fxPos = (int) ceil((imagesx($img) - $textWidth) / 2);
	$fyPos = (int) ceil((imagesy($img) - $fontHeight) / 2);

	// Add the image text
	imagestring($img, $fontSize, $fxPos, $fyPos, $text, $textColor);

	// Save the images
	imagesavealpha($img, TRUE);
	imagepng($img, $path . '/placeholder.png', 9);
	imagedestroy($img);

	$pngImage = imagecreatefrompng($path . '/placeholder.png');
	imagealphablending($pngImage, TRUE);
	imagesavealpha($pngImage, TRUE);

	imagewebp($pngImage, $path . '/placeholder.webp');

	imagedestroy($pngImage);
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
 * @throws InvalidArgumentException
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

	$saved = ( ! empty($userData))
		? $cache->setMultiple($userData)
		: TRUE;

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
	return ob_get_clean();
}