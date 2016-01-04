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

use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;
use InvalidArgumentException;
use BadMethodCallException;

/**
 * Friend class for testing
 */
class Friend {

	/**
	 * Object to create a friend of
	 * @var object
	 */
	private $_friend_;

	/**
	 * Reflection class of the object
	 * @var object
	 */
	private $_reflect_;

	/**
	 * Create a friend object
	 *
	 * @param object $obj
	 */
	public function __construct($obj)
	{
		if ( ! is_object($obj))
		{
			throw new InvalidArgumentException("Friend must be an object");
		}

		$this->_friend_ = $obj;
		$this->_reflect_ = new ReflectionClass($obj);
	}

	/**
	 * Retrieve a friend's property
	 *
	 * @param  string $key
	 * @return mixed
	 */
	public function __get($key)
	{
		if ($this->_reflect_->hasProperty($key))
		{
			$property = $this->_get_property($key);
			return $property->getValue($this->_friend_);
		}

		return NULL;
	}

	/**
	 * Set a friend's property
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public function __set($key, $value)
	{
		if ($this->_reflect_->hasProperty($key))
		{
			$property = $this->_get_property($key);
			$property->setValue($this->_friend_, $value);
		}
	}

	/**
	 * Calls a protected or private method on the friend
	 *
	 * @param  string $method
	 * @param  array $args
	 * @return mixed
	 */
	public function __call($method, $args)
	{
		if ( ! $this->_reflect_->hasMethod($method))
		{
			throw new BadMethodCallException("Method '{$method}' does not exist");
		}

		$friendMethod = new ReflectionMethod($this->_friend_, $method);
		$friendMethod->setAccessible(TRUE);
		return $friendMethod->invokeArgs($this->_friend_, $args);
	}

	/**
	 * Iterates over parent classes to get a ReflectionProperty
	 *
	 * @codeCoverageIgnore
	 * @param  string $name
	 * @return ReflectionProperty|null
	 */
	private function _get_property($name)
	{
		try
		{
			$property = $this->_reflect_->getProperty($name);
			$property->setAccessible(TRUE);
			return $property;
		}
		// Return NULL on any exception, so no further logic needed
		// in the catch block
		catch (\Exception $e)
		{
			return NULL;
		}
	}
}
// End of Friend.php