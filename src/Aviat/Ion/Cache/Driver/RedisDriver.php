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

class RedisDriver implements \Aviat\Ion\Cache\CacheDriverInterface {
	
	/**
	 * The redis extension class instance
	 * #var Redis
	 */
	protected $redis;
	
	/**
	 * Create the Redis cache driver
	 */
	public function __construct(ContainerInterface $container)
	{
		$config = $container->get('config');
		$redisConfig = $config->get('redis');
		
		$this->redis = new \Redis();
		
		(array_key_exists('port', $redisConfig))
			? $this->redis->pconnect($redisConfig['host'], $redisConfig['port'])
			: $this->redis->pconnect($redisConfig['host']);
		
		// If there is a password, authorize
		if (array_key_exists('password', $redisConfig))
		{
			$this->redis->auth($redisConfig['password']);
		}
		
		// If there is a database selected, connect to the specified database
		if (array_key_exists('database', $redisConfig))
		{
			$this->redis->select($redisConfig['database']);
		}
		
		$this->redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);
	}
	
	/**
	 * Destructor to disconnect from redis
	 */
	public function __destruct()
	{
		$this->redis->close();
	}
	
	/**
	 * Retreive a value from the cache backend
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get($key)
	{
		return $this->redis->get($key);
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
		$this->redis->set($key, $value);
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