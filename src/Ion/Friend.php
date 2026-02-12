<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.4
 *
 * @copyright   2015 - 2026  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.3
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\Ion;

use BadMethodCallException;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

use function is_object;

/**
 * Friend class for testing
 * @template F
 */
class Friend
{
	/**
	 * Object to create a friend of
	 * @var F
	 */
	private mixed $_friend_;

	/**
	 * Reflection class of the object
	 * @var ReflectionClass<F> $_reflect
	 * @phpstan-ignore missingType.generics
	 */
	private ReflectionClass $_reflect_;

	/**
	 * Create a friend object
	 *
	 * @throws InvalidArgumentException
	 * @throws ReflectionException
	 */
	public function __construct(mixed $obj)
	{
		if (! is_object($obj))
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

			if ($property !== null)
			{
				return $property->getValue($this->_friend_);
			}
		}

		return null;
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
			$this->_get_property($key)?->setValue($this->_friend_, $value);
		}
	}

	/**
	 * Calls a protected or private method on the friend
	 *
	 * @param array<string, mixed>|list<mixed> $args
	 * @throws BadMethodCallException
	 * @throws ReflectionException
	 * @return mixed
	 */
	public function __call(string $method, array $args)
	{
		if (! $this->_reflect_->hasMethod($method))
		{
			throw new BadMethodCallException("Method '{$method}' does not exist");
		}

		$friendMethod = new ReflectionMethod($this->_friend_, $method);

		return $friendMethod->invokeArgs($this->_friend_, $args);
	}

	/**
	 * Iterates over parent classes to get a ReflectionProperty
	 */
	private function _get_property(string $name): null|ReflectionProperty
	{
		try {
			// $property->setAccessible(TRUE);

			return $this->_reflect_->getProperty($name);
		}
		// Return NULL on any exception, so no further logic needed
		// in the catch block
		// @codeCoverageIgnoreStart
		catch (\Exception) {
			return null;
		}

		// @codeCoverageIgnoreEnd
	}
}

// End of Friend.php
