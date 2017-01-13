<?php declare(strict_types=1);
/**
 * Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     AnimeListClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2017  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API;

use Aviat\Banker\Pool;
use Aviat\Ion\Di\ContainerAware;

/**
 * Helper methods for dealing with the Cache
 */
trait CacheTrait {
	
	/**
	 * @var Aviat\Banker\Pool
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
	 * Generate a hash as a cache key from the current method call
	 *
	 * @param object $object
	 * @param string $method
	 * @param array  $args
	 * @return string
	 */
	public function getHashForMethodCall($object, string $method, array $args = []): string
	{
		$classname = get_class($object);
		$keyObj = [
			'class' => $classname,
			'method' => $method,
			'args' => $args,
		];
		$hash = sha1(json_encode($keyObj));
		return $hash;
	}
}