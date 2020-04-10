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

use Aviat\Banker\Pool;

/**
 * Helper methods for dealing with the Cache
 */
trait CacheTrait {

	/**
	 * @var Pool
	 */
	protected $cache;

	/**
	 * Inject the cache object
	 *
	 * @param Pool $cache
	 * @return $this
	 */
	public function setCache(Pool $cache): self
	{
		$this->cache = $cache;
		return $this;
	}

	/**
	 * Get the cache object if it exists
	 *
	 * @return Pool
	 */
	public function getCache(): Pool
	{
		return $this->cache;
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
			'class' => \get_class($object),
			'method' => $method,
			'args' => $args,
		];
		return sha1(json_encode($keyObj));
	}
}