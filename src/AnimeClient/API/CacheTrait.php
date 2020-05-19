<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API;

use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Helper methods for dealing with the Cache
 */
trait CacheTrait {

	/**
	 * @var CacheInterface
	 */
	protected CacheInterface $cache;

	/**
	 * Inject the cache object
	 *
	 * @param CacheInterface $cache
	 * @return $this
	 */
	public function setCache(CacheInterface $cache): self
	{
		$this->cache = $cache;
		return $this;
	}

	/**
	 * Get the cache object if it exists
	 *
	 * @return CacheInterface
	 */
	public function getCache(): CacheInterface
	{
		return $this->cache;
	}

	/**
	 * Get the cached value if it exists, otherwise set the cache value
	 * and return it.
	 *
	 * @param string $key
	 * @param callable $primer
	 * @param array $primeArgs
	 * @return mixed|null
	 * @throws InvalidArgumentException
	 */
	public function getCached(string $key, callable $primer, ?array $primeArgs = [])
	{
		$value = $this->cache->get($key, NULL);

		if ($value === NULL)
		{
			$value = $primer(...$primeArgs);
			if ($value === NULL)
			{
				return NULL;
			}

			$this->cache->set($key, $value);
		}

		return $value;
	}

	/**
	 * Generate a hash as a cache key from the current method call
	 *
	 * @param mixed $object
	 * @param string $method
	 * @param array  $args
	 * @return string
	 */
	public function getHashForMethodCall($object, string $method, array $args = []): string
	{
		$keyObj = [
			'class' => get_class($object),
			'method' => $method,
			'args' => $args,
		];
		return sha1(json_encode($keyObj));
	}
}