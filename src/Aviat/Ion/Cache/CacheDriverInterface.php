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
 * Interface for cache drivers
 */
interface CacheDriverInterface {
    /**
     * Retreive a value from the cache backend
     *
     * @param string $key
     * @return mixed
     */
    public function get($key);

    /**
     * Set a cached value
     *
     * @param string $key
     * @param mixed $value
     * @return CacheDriverInterface
     */
    public function set($key, $value);

    /**
     * Invalidate a cached value
     *
     * @param string $key
     * @return CacheDriverInterface
     */
    public function invalidate($key);

    /**
     * Clear the contents of the cache
     *
     * @return void
     */
    public function invalidateAll();
}
// End of CacheDriverInterface.php