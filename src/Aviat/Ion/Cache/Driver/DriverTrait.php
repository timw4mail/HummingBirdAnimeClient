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

use Aviat\Ion\Json;
use Aviat\Ion\JsonException;

/**
 * Abstract base for Cache drivers to share common functionality
 */
trait DriverTrait  {

	/**
	 * Key prefix for key / value cache stores
	 */
	protected static $CACHE_KEY_PREFIX = "hbac:cache:";

	/**
	 * Set key prefix for cache drivers that have global keys
	 *
	 * @param string $key - the raw key name
	 * @return string - the prefixed key name
	 */
	protected function prefix($key)
	{
		return static::$CACHE_KEY_PREFIX . $key;
	}

	/**
	 * Converts data to cache to a string representation for storage in a cache
	 *
	 * @param mixed $data - data to store in the cache backend
	 * @return string
	 */
	protected function serialize($data)
	{
		return Json::encode($data);
	}

	/**
	 * Convert serialized data from cache backend to native types
	 *
	 * @param string $data - data from cache backend
	 * @return mixed
	 * @throws JsonException
	 */
	protected function unserialize($data)
	{
		return Json::decode($data);
	}
}
// End of DriverTrait.php