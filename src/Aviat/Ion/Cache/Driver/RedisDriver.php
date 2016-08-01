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

use Aviat\Ion\ConfigInterface;

use Predis\Client;

/**
 * Cache Driver for a Redis backend
 */
class RedisDriver implements DriverInterface {

	use DriverTrait;
	
	/**
	 * THe Predis library instance
	 *
	 * @var Client
	 */
	protected $redis;
	
	/**
	 * Create the Redis cache driver
	 *
	 * @param ConfigInterface $config The configuration management class
	 */
	public function __construct(ConfigInterface $config)
	{
		$redisConfig = $config->get('redis');

		// If you don't have a redis password set, and you attempt to send an
		// empty string, Redis will think you want to authenticate with a password
		// that is an empty string. To work around this, empty string passwords
		// are considered to be a lack of a password
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
	 * Retrieve a value from the cache backend
	 *
	 * @param string $rawKey
	 * @return mixed
	 */
	public function get($rawKey)
	{
		$key = $this->prefix($rawKey);
		$serializedData = $this->redis->get($key);

		return $this->unserialize($serializedData);
	}
	
	/**
	 * Set a cached value
	 *
	 * @param string $rawKey
	 * @param mixed $value
	 * @return DriverInterface
	 */
	public function set($rawKey, $value)
	{
		$key = $this->prefix($rawKey);
		$serializedData = $this->serialize($value);

		$this->redis->set($key, $serializedData);

		return $this;
	}
	
	/**
	 * Invalidate a cached value
	 *
	 * @param string $rawKey
	 * @return DriverInterface
	 */
	public function invalidate($rawKey)
	{
		$key = $this->prefix($rawKey);
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
		$this->redis->flushdb();
	}
}
// End of RedisDriver.php