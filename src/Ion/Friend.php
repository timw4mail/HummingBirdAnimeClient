<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2021  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\Ion;

use BadMethodCallException;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;
use ReflectionProperty;

/**
 * Friend class for testing
 */
class Friend {

	/**
	 * Object to create a friend of
	 */
	private mixed $_friend_;

	/**
	 * Reflection class of the object
	 */
	private ReflectionClass $_reflect_;

	/**
	 * Create a friend object
	 *
	 * @throws InvalidArgumentException
	 * @throws \ReflectionException
	 */
	public function __construct(mixed $obj)
	{
		if ( ! \is_object($obj))
		{
			throw new InvalidArgumentException('Friend must be an object');
		}

		$this->_friend_ = $obj;
		$this->_reflect_ = new ReflectionClass($obj);
	}

	/**
	 * Retrieve a friend's property
	 */
	public function __get(string $key): mixed
	{
		if ($this->__isset($key))
		{
			$property = $this->_get_property($key);

			if ($property !== NULL)
			{
				return $property->getValue($this->_friend_);
			}
		}

		return NULL;
	}

	/**
	 * See if a property exists on the friend
	 */
	public function __isset(string $name): bool
	{
		return $this->_reflect_->hasProperty($name);
	}

	/**
	 * Set a friend's property
	 *
	 * @return void
	 */
	public function __set(string $key, mixed $value)
	{
		if ($this->__isset($key))
		{
			$property = $this->_get_property($key);

			if ($property !== NULL)
			{
				$property->setValue($this->_friend_, $value);
			}
		}
	}

	/**
	 * Calls a protected or private method on the friend
	 *
	 * @return mixed
	 * @throws BadMethodCallException
	 * @throws \ReflectionException
	 */
	public function __call(string $method, array $args)
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
	 */
	private function _get_property(string $name): ?ReflectionProperty
	{
		try
		{
			$property = $this->_reflect_->getProperty($name);
			$property->setAccessible(TRUE);
			return $property;
		}
		// Return NULL on any exception, so no further logic needed
		// in the catch block
		// @codeCoverageIgnoreStart
		catch (\Exception)
		{
			return NULL;
		}

		// @codeCoverageIgnoreEnd
	}
}

// End of Friend.php