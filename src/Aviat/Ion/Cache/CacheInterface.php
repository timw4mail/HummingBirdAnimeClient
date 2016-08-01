<?php
/**
 * Ion
 *
 * Building blocks for web development
 *
 * @package     Ion
 * @author      Timothy J. Warren
 * @copyright   Copyright (c) 2015 - 2016
 * @license     MIT
 */

namespace Aviat\Ion\Cache;

/**
 * Interface for retrieving values from cache
 */
interface CacheInterface {

	/**
	 * Retrieve a cached value if it exists, otherwise, get the value
	 * from the passed arguments
	 *
	 * @param object $object - object to retrieve fresh value from
	 * @param string $method - method name to call
	 * @param [array] $args - the arguments to pass to the retrieval method
	 * @return mixed - the cached or fresh data
	 */
	public function get($object, $method, array $args=[]);

	/**
	 * Retrieve a fresh value, and update the cache
	 *
	 * @param object $object - object to retrieve fresh value from
	 * @param string $method - method name to call
	 * @param [array] $args - the arguments to pass to the retrieval method
	 * @return mixed - the fresh data
	 */
	public function getFresh($object, $method, array $args=[]);

	/**
	 * Clear the entire cache
	 *
	 * @return void
	 */
	public function purge();
}
// End of CacheInterface.php