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

namespace Aviat\Ion;

/**
 * Trait to allow calling a method statically,
 * as well as with an instance
 */
trait StaticInstance {
	/**
	 * Instance for 'faking' static methods
	 *
	 * @var object
	 */
	private static $instance = [];

	/**
	 * Call methods protected to allow for
	 * static and instance calling
	 *
	 * @codeCoverageIgnore
	 * @param string $method
	 * @param array $args
	 * @retun mixed
	 */
	public function __call($method, $args)
	{
		if (method_exists($this, $method))
		{
			return call_user_func_array([$this, $method], $args);
		}
	}

	/**
	 * Call non-static methods statically, so that
	 * an instance of the class isn't required
	 *
	 * @param string $method
	 * @param array $args
	 * @return mixed
	 */
	public static function __callStatic($method, $args)
	{
		$class = get_called_class();
		if ( ! array_key_exists($class, self::$instance))
		{
			self::$instance[$class] = new $class();
		}

		return call_user_func_array([self::$instance[$class], $method], $args);
	}
}
// End of StaticInstance.php