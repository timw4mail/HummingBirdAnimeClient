<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshome.page>
 * @copyright   2015 - 2022  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
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
	 */
	public function __construct(array $configArray = [])
	{
		$this->map = ArrayType::from($configArray);
	}

	/**
	 * Does the config item exist?
	 */
	public function has(array|int|string $key): bool
	{
		return $this->map->hasKey($key);
	}

	/**
	 * Get a config value
	 *
	 * @throws ConfigException
	 */
	public function get(array|string $key = NULL): mixed
	{
		if (is_array($key))
		{
			return $this->map->getDeepKey($key);
		}

		return $this->map->get($key);
	}

	/**
	 * Remove a config value
	 */
	public function delete(array|string $key): void
	{
		if (is_array($key))
		{
			$this->map->setDeepKey($key, NULL);
		}
		else
		{
			$pos =& $this->map->get($key);
			$pos = NULL;
		}
	}

	/**
	 * Set a config value
	 *
	 *@throws InvalidArgumentException
	 */
	public function set(array|int|string $key, mixed $value): ConfigInterface
	{
		if (is_array($key))
		{
			$this->map->setDeepKey($key, $value);
		}
		elseif (is_scalar($key) && ! empty($key))
		{
			$this->map->set($key, $value);
		}
		else
		{
			throw new InvalidArgumentException('Key must be integer, string, or array, and cannot be empty');
		}

		return $this;
	}
}

// End of config.php
