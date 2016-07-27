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
use Aviat\Ion\Cache\CacheDriverInterface;

/**
 * The Driver for no real cache
 */
class NullDriver implements CacheDriverInterface {

	/**
	 * 'Cache' for Null data store
	 */
	protected $data;

	/**
	 * Create the Null cache driver
	 *
	 * @param ConfigInterface $config The configuration management class
	 */
	public function __construct(ConfigInterface $config)
	{
		$this->data = [];
	}

	/**
	 * Retreive a value from the cache backend
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get($key)
	{
		return (array_key_exists($key, $this->data))
			? $this->data[$key]
			: NULL;
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
		$this->data[$key] = $value;
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
		unset($this->data[$key]);
		return $this;
	}

	/**
	 * Clear the contents of the cache
	 *
	 * @return void
	 */
	public function invalidateAll()
	{
		$this->data = [];
	}
}
// End of NullDriver.php