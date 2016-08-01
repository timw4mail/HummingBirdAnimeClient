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

use Aviat\Ion\ConfigInterface;
use Aviat\Ion\Cache\Driver\DriverInterface;

/**
 * Class proxying cached and fresh values from the selected cache driver
 */
class CacheManager implements CacheInterface {

	/**
	 * @var DriverInterface
	 */
	protected $driver;

	/**
	 * Retrieve the appropriate driver from the container
	 *
	 * @param ConfigInterface $config The configuration management class
	 */
	public function __construct(ConfigInterface $config)
	{
		$driverConf = $config->get('cache_driver');

		if (empty($driverConf))
		{
			$driverConf = 'NullDriver';
		}

		$driverClass = __NAMESPACE__ . "\\Driver\\{$driverConf}";
		$driver = new $driverClass($config);

		$this->driver = $driver;
	}

	/**
	 * Retrieve a cached value if it exists, otherwise, get the value
	 * from the passed arguments
	 *
	 * @param object $object - object to retrieve fresh value from
	 * @param string $method - method name to call
	 * @param [array] $args - the arguments to pass to the retrieval method
	 * @return mixed - the cached or fresh data
	 */
	public function get($object, $method, array $args=[])
	{
		$hash = $this->generateHashForMethod($object, $method, $args);

		$data = $this->driver->get($hash);

		if (empty($data))
		{
			$data = call_user_func_array([$object, $method], $args);
			$this->driver->set($hash, $data);
		}

		return $data;
	}

	/**
	 * Retrieve a fresh value from the method, and update the cache
	 * @param object $object - object to retrieve fresh value from
	 * @param string $method - method name to call
	 * @param [array] $args - the arguments to pass to the retrieval method
	 * @return mixed - the fresh data
	 */
	public function getFresh($object, $method, array $args=[])
	{
		$hash = $this->generateHashForMethod($object, $method, $args);
		$data = call_user_func_array([$object, $method], $args);
		$this->driver->set($hash, $data);
		return $data;
	}

	/**
	 * Clear the entire cache
	 *
	 * @return void
	 */
	public function purge()
	{
		$this->driver->invalidateAll();
	}

	/**
	 * Generate a hash as a cache key from the current method call
	 *
	 * @param object $object
	 * @param string $method
	 * @param array $args
	 * @return string
	 */
	protected function generateHashForMethod($object, $method, array $args)
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
// End of CacheManager.php