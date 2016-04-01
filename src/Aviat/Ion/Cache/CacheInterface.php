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
	 * Retreive a cached value if it exists, otherwise, get the value
	 * from the passed arguments
	 *
	 * @param object $object - object to retrieve fresh value from
	 * @param string $method - method name to call
	 * @param array $args - the arguments to pass to the retrieval method
	 * @return mixed - the cached or fresh data
	 */ 
	public function get($object, $method, array $args);
}
// End of CacheInterface.php