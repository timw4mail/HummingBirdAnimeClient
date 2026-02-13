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

namespace Aviat\Ion;

use Aviat\Ion\Exception\ConfigException;
use Aviat\Ion\Type\ArrayType;
use InvalidArgumentException;

use function is_array;

/**
 * Wrapper for configuration values
 */
class Config implements ConfigInterface
{
	/**
	 * Config object
	 */
	protected ArrayType $map;

	/**
	 * Constructor
	 *
	 * @param array<string, mixed> $configArray
	 */
	public function __construct(array $configArray = [])
	{
		$this->map = ArrayType::from($configArray);
	}

	/**
	 * Does the config item exist?
	 * @param array<string|int>|int|string $key
	 */
	#[\Override]
	public function has(array|int|string $key): bool
	{
		return $this->map->hasKey($key);
	}

	/**
	 * Get a config value
	 *
	 * @param array<string|int>|int|string|null $key
	 * @throws ConfigException
	 */
	#[\Override]
	public function get(array|int|string|null $key = null): mixed
	{
		if (is_array($key))
		{
			return $this->map->getDeepKey($key);
		}

		return $this->map->get($key);
	}

	/**
	 * Remove a config value
	 * @param array<string|int>|int|string $key
	 */
	#[\Override]
	public function delete(array|int|string $key): void
	{
		if (is_array($key))
		{
			$this->map->setDeepKey($key, null);
		}
		else
		{
			$pos = &$this->map->get($key);
			$pos = null;
		}
	}

	/**
	 * Set a config value
	 *
	 *@throws InvalidArgumentException
	 */
	#[\Override]
	public function set(array|int|string $key, mixed $value): ConfigInterface
	{
		match (true)
		{
			is_array($key) => $this->map->setDeepKey($key, $value),
			is_string($key) && $key !== '', is_int($key) => $this->map->set($key, $value),
			default => throw new InvalidArgumentException(
				'Key must be integer, string, or array, and cannot be empty',
			),
		};

		return $this;
	}
}

// End of config.php
