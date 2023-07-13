<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @copyright   2015 - 2022  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshome.page/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API;

use Psr\SimpleCache\{CacheInterface, InvalidArgumentException};

/**
 * Helper methods for dealing with the Cache
 */
trait CacheTrait
{
	protected CacheInterface $cache;

	/**
	 * Inject the cache object
	 */
	public function setCache(CacheInterface $cache): self
	{
		$this->cache = $cache;

		return $this;
	}

	/**
	 * Get the cache object if it exists
	 */
	public function getCache(): CacheInterface
	{
		return $this->cache;
	}

	/**
	 * Get the cached value if it exists, otherwise set the cache value
	 * and return it.
	 * @throws InvalidArgumentException
	 */
	public function getCached(string $key, callable $primer, ?array $primeArgs = []): mixed
	{
		$value = $this->cache->get($key);

		if ($value === NULL)
		{
			$primeArgs ??= [];
			$value = $primer(...$primeArgs);
			if ($value === NULL)
			{
				return NULL;
			}

			$this->cache->set($key, $value);
		}

		return $value;
	}
}
