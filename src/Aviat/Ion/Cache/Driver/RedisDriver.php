<?php
/**
 * Ion
 *
 * Building blocks for web development
 *
 * @package	 Ion
 * @author	  Timothy J. Warren
 * @copyright   Copyright (c) 2015 - 2016
 * @license	 MIT
 */

namespace Aviat\Ion\Cache\Driver;

use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Cache\CacheDriverInterface;

use Predis\Client;

class RedisDriver implements CacheDriverInterface {
	
	/**
	 * The redis extension class instance
	 * @var Redis
	 */
	protected $redis;
	
	/**
	 * Create the Redis cache driver
	 */
	public function __construct(ContainerInterface $container)
	{
		$config = $container->get('config');
		$redisConfig = $config->get('redis');

		if (array_key_exists('password', $redisConfig) && $redisConfig['password'] === '')
		{
			unset($redisConfig['password']);
		}
		
		$this->redis = new Client($redisConfig);
	}

	/**
	 * Disconnect from redis
	 */
	public function __destruct()
	{
		$this->redis = null;
	}
	
	/**
	 * Retreive a value from the cache backend
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get($key)
	{
		return unserialize($this->redis->get($key));
	}
	
	/**
	 * Set a cached value
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return CacheDriverInterface
	 */
	public function set($key, $value)
	{
		$this->redis->set($key, serialize($value));
		return $this;
	}
	
	/**
	 * Invalidate a cached value
	 *
	 * @param string $key
	 * @return CacheDriverInterface
	 */
	public function invalidate($key)
	{
		$this->redis->del($key);
		return $this;
	}
	
	/**
	 * Clear the contents of the cache
	 *
	 * @return void
	 */
	public function invalidateAll()
	{
		$this->redis->flushDB();
	}
}
// End of RedisDriver.php