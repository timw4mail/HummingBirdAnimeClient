<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2018  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Types;

use ArrayAccess;
use LogicException;

abstract class AbstractType implements ArrayAccess {
	/**
	 * Populate values for unserializing data
	 *
	 * @param $properties
	 * @return mixed
	 */
	public static function __set_state($properties)
	{
		return new static($properties);
	}

	/**
	 * Sets the properties by using the constructor
	 *
	 * @param mixed $data
	 */
	public function __construct($data = [])
	{
		$typeKeys = array_keys((array)$this);
		$dataKeys = array_keys((array)$data);

		$unsetKeys = array_diff($typeKeys, $dataKeys);

		foreach ($data as $key => $value)
		{
			$this->__set($key, $value);
		}

		// Remove unset keys so that they aren't serialized
		foreach ($unsetKeys as $k)
		{
			unset($this->$k);
		}
	}

	/**
	 * See if a property is set
	 *
	 * @param $name
	 * @return bool
	 */
	public function __isset($name): bool
	{
		return property_exists($this, $name);
	}

	/**
	 * Set a property on the type object
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	public function __set($name, $value): void
	{
		$setterMethod = 'set' . ucfirst($name);

		if (method_exists($this, $setterMethod))
		{
			$this->$setterMethod($value);
			return;
		}

		if ( ! property_exists($this, $name))
		{
			$existing = json_encode($this);

			throw new LogicException("Trying to set non-existent property: '$name'. Existing properties: $existing");
		}

		$this->$name = $value;
	}

	/**
	 * Get a property from the type object
	 *
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{
		if (property_exists($this, $name))
		{
			return $this->$name;
		}

		throw new LogicException("Trying to get non-existent property: '$name'");
	}

	/**
	 * Implementing ArrayAccess
	 *
	 * @param $offset
	 * @return bool
	 */
	public function offsetExists($offset): bool
	{
		return $this->__isset($offset);
	}

	/**
	 * Implementing ArrayAccess
	 *
	 * @param $offset
	 * @return mixed
	 */
	public function offsetGet($offset)
	{
		return $this->__get($offset);
	}

	/**
	 * Implementing ArrayAccess
	 *
	 * @param $offset
	 * @param $value
	 */
	public function offsetSet($offset, $value): void
	{
		$this->__set($offset, $value);
	}

	/**
	 * Implementing ArrayAccess
	 *
	 * @param $offset
	 */
	public function offsetUnset($offset): void
	{
		if ($this->offsetExists($offset))
		{
			unset($this->$offset);
		}
	}

	/**
	 * Recursively cast properties to an array
	 *
	 * @param null $parent
	 * @return mixed
	 */
	public function toArray($parent = null)
	{
		$object = $parent ?? $this;

		if (is_scalar($object))
		{
			return $object;
		}

		$output = [];

		foreach ($object as $key => $value)
		{
			$output[$key] = is_scalar($value)
				? $value
				: $this->toArray((array) $value);
		}

		return $output;
	}
}