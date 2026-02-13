<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.4
 *
 * @copyright   2015 - 2026  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.3
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient;

use Amp\Future;
use Amp\Http\Client\HttpClient;
use Amp\Http\Client\HttpClientBuilder;
use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Aviat\Ion\ConfigInterface;
use Aviat\Ion\ImageBuilder;
use DateTimeImmutable;
use Devium\Toml\Toml;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Throwable;

use function Amp\async;
use function Aviat\Ion\_dir;

const SECONDS_IN_MINUTE = 60;

const MINUTES_IN_HOUR = 60;

const MINUTES_IN_DAY = 1440;

const MINUTES_IN_YEAR = 525_600;

// ----------------------------------------------------------------------------
//! TOML Functions
// ----------------------------------------------------------------------------
/**
 * Load configuration options from .toml files
 *
 * @param string $path - Path to load config
 * @return array<string, mixed>
 */
function loadConfig(string $path): array
{
	$output = [];
	$files = glob("{$path}/*.toml");

	if (! is_array($files))
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

		$config = loadTomlFile($file);

		if ($key === 'config')
		{
			foreach ($config as $name => $value)
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
 * @return array<string, mixed>
 */
function loadTomlFile(string $filename): array
{
	if (! is_file($filename))
	{
		return [];
	}

	$file = file_get_contents($filename) ?? '';

	return tomlToArray($file);
}

/**
 * Serialize config data into a Toml file
 *
 * @param iterable<mixed> $data
 */
function arrayToToml(iterable $data): string
{
	return Toml::encode($data);
}

/**
 * Serialize toml back to an array
 *
 * @return array<string, mixed>
 */
function tomlToArray(string $toml): array
{
	return Toml::decode($toml, asArray: true);
}

// ----------------------------------------------------------------------------
//! Misc Functions
// ----------------------------------------------------------------------------

/**
 * Is the array sequential, not associative?
 */
function isSequentialArray(mixed $array): bool
{
	return is_array($array) && array_is_list($array);
}

/**
 * Check that folder permissions are correct for proper operation
 *
 * @return array<string, mixed>
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
		if (! is_dir($actual))
		{
			$errors['missing'][] = $pretty;

			continue;
		}

		$writable = is_writable($actual) && is_executable($actual);

		if (! $writable)
		{
			$errors['writable'][] = $pretty;
		}
	}

	return $errors;
}

/**
 * Get an API Client, with better defaults
 */
function getApiClient(null|HttpClient $newClient = null): HttpClient
{
	static $client;

	if ($newClient !== null)
	{
		$client = $newClient;
	}

	if ($client === null)
	{
		$client = HttpClientBuilder::buildDefault();
	}

	return $client;
}

/**
 * Simplify making a request with Http\Client
 *
 * @throws Throwable
 */
function getResponse(Request|string $request): Response
{
	$client = getApiClient();

	if (is_string($request))
	{
		$request = new Request($request);
	}

	$future = async(static fn () => $client->request($request));

	[$response] = Future\await([$future]);

	return $response;
}

/**
 * Generate the path for the cached image from the original image
 */
function getLocalImg(string $kitsuUrl, bool $webp = true): string
{
	if ($kitsuUrl === '')
	{
		return 'images/placeholder.webp';
	}

	$parts = parse_url($kitsuUrl);

	if ($parts === false || ! array_key_exists('path', $parts))
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
 */
function createPlaceholderImage(
	string $path,
	int $width = 200,
	int $height = 200,
	string $text = 'Image Unavailable',
): bool {
	$img = ImageBuilder::new($width, $height)
		->enableAlphaBlending(true)
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
 * @param array<string, mixed> $search
 */
function colNotEmpty(array $search, string $key): bool
{
	// @mago-expect lint:no-empty
	$items = array_filter(array_column($search, $key), static fn ($x) => ! empty($x));

	return $items !== [];
}

/**
 * Clear the cache, but save user auth data
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

	$userData = array_filter((array) $userData, static fn ($value) => $value !== null);

	$cleared = $cache->clear();

	$saved = $userData === [] || $cache->setMultiple($userData);

	return $cleared && $saved;
}

/**
 * Render a PHP code template as a string
 *
 * @param array<string, mixed> $data
 */
function renderTemplate(string $path, array $data): string
{
	ob_start();
	extract($data, EXTR_OVERWRITE);
	include $path;
	$rawOutput = ob_get_clean();

	return is_string($rawOutput) ? $rawOutput : '';
}

function formatDate(string $date): string
{
	$date = new DateTimeImmutable($date);

	return $date->format('F d, Y');
}

function getDateDiff(string $date): int
{
	$now = new DateTimeImmutable();
	$then = new DateTimeImmutable($date);

	$interval = $now->diff($then, true);

	$years = $interval->y * SECONDS_IN_MINUTE * MINUTES_IN_YEAR;
	$days = $interval->d * SECONDS_IN_MINUTE * MINUTES_IN_DAY;
	$hours = $interval->h * SECONDS_IN_MINUTE * MINUTES_IN_HOUR;
	$minutes = $interval->i * SECONDS_IN_MINUTE;
	$seconds = $interval->s;

	return $years + $days + $hours + $minutes + $seconds;
}

/**
 * Convert a time in seconds to a more human-readable format
 */
function friendlyTime(int $seconds, string $minUnit = 'second'): string
{
	// All the seconds left
	$remSeconds = $seconds % SECONDS_IN_MINUTE;
	$minutes = ($seconds - $remSeconds) / SECONDS_IN_MINUTE;

	// Minutes short of a year
	$years = (int) floor($minutes / MINUTES_IN_YEAR);
	$minutes %= MINUTES_IN_YEAR;

	// Minutes short of a day
	$extraMinutes = $minutes % MINUTES_IN_DAY;
	$days = ($minutes - $extraMinutes) / MINUTES_IN_DAY;

	// Minutes short of an hour
	$remMinutes = $extraMinutes % MINUTES_IN_HOUR;
	$hours = ($extraMinutes - $remMinutes) / MINUTES_IN_HOUR;

	$parts = [];

	foreach ([
		'year' => $years,
		'day' => $days,
		'hour' => $hours,
		'minute' => $remMinutes,
		'second' => $remSeconds,
	] as $label => $value)
	{
		if ($value === 0)
		{
			continue;
		}

		if ($value > 1)
		{
			$label .= 's';
		}

		$parts[] = "{$value} {$label}";

		if ($label === $minUnit || $label === $minUnit . 's')
		{
			break;
		}
	}

	$last = array_pop($parts);

	if ($parts === [])
	{
		return $last;
	}

	return count($parts) > 1
		? implode(', ', $parts) . ", and {$last}"
		: "{$parts[0]}, {$last}";
}
