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

namespace Aviat\AnimeClient\Types;

use ArrayAccess;
use Countable;

abstract class AbstractType implements ArrayAccess, Countable {
	/**
	 * Populate values for un-serializing data
	 *
	 * @param mixed $properties
	 * @return self
	 */
	public static function __set_state(mixed $properties): self
	{
		return new static($properties);
	}

	/**
	 * Check the shape of the object, and return the array equivalent
	 *
	 * @param array $data
	 * @return array|null
	 */
	final public static function check($data = []): ?array
	{
		$currentClass = static::class;

		if (get_parent_class($currentClass) !== FALSE)
		{
			return static::class::from($data)->toArray();
		}

		return NULL;
	}

	/**
	 * Static constructor
	 *
	 * @param mixed $data
	 * @return static
	 */
	final public static function from(mixed $data): static
	{
		return new static($data);
	}

	/**
	 * Sets the properties by using the constructor
	 *
	 * @param mixed $data
	 */
	final private function __construct(mixed $data = [])
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
	 * @param string $name
	 * @return bool
	 */
	final public function __isset(string $name): bool
	{
		return property_exists($this, $name) && isset($this->$name);
	}

	/**
	 * Set a property on the type object
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return void
	 */
	final public function __set(string $name, mixed $value): void
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

			throw new UndefinedPropertyException("Trying to set undefined property: '$name'. Existing properties: $existing");
		}

		$this->$name = $value;
	}

	/**
	 * Get a property from the type object
	 *
	 * @param string $name
	 * @return mixed
	 */
	final public function __get(string $name): mixed
	{
		// Be a bit more lenient here, so that you can easily typecast missing
		// values to reasonable defaults, and not have to resort to array indexes
		return ($this->__isset($name)) ? $this->$name : NULL;
	}

	/**
	 * Create a string representation of the object for debugging
	 *
	 * @return string
	 */
	public function __toString(): string
	{
		return print_r($this, TRUE);
	}

	/**
	 * Implementing ArrayAccess
	 *
	 * @param mixed $offset
	 * @return bool
	 */
	final public function offsetExists(mixed $offset): bool
	{
		return $this->__isset((string)$offset);
	}

	/**
	 * Implementing ArrayAccess
	 *
	 * @param mixed $offset
	 * @return mixed
	 */
	final public function offsetGet(mixed $offset): mixed
	{
		return $this->__get((string)$offset);
	}

	/**
	 * Implementing ArrayAccess
	 *
	 * @param mixed $offset
	 * @param mixed $value
	 */
	final public function offsetSet(mixed $offset, mixed $value): void
	{
		$this->__set((string)$offset, $value);
	}

	/**
	 * Implementing ArrayAccess
	 *
	 * @param mixed $offset
	 */
	final public function offsetUnset(mixed $offset): void
	{
		if ($this->offsetExists($offset))
		{
			$strOffset = (string)$offset;
			unset($this->$strOffset);
		}
	}

	/**
	 * Implementing Countable
	 *
	 * @return int
	 */
	final public function count(): int
	{
		$keys = array_keys((array)$this->toArray());
		return count($keys);
	}

	/**
	 * Recursively cast properties to an array
	 *
	 * Returns early on primitive values to work recursively.
	 *
	 * @param mixed $parent
	 * @return array
	 */
	final public function toArray(mixed $parent = null): array
	{
		$fromObject = $this->fromObject($parent);
		return (is_array($fromObject)) ? $fromObject : [];
	}

	/**
	 * Determine whether the type has any properties set
	 *
	 * @return bool
	 */
	final public function isEmpty(): bool
	{
		$self = (array)$this->toArray();
		foreach ($self as $value)
		{
			if ( ! empty($value))
			{
				return FALSE;
			}
		}

		return TRUE;
	}

	/**
	 * @codeCoverageIgnore
	 */
	final protected function fromObject(mixed $parent = null): float|null|bool|int|array|string
	{
		$object = $parent ?? $this;

		if (is_scalar($object) || $object === NULL)
		{
			return $object;
		}

		$output = [];

		foreach ($object as $key => $value)
		{
			$output[$key] = (is_scalar($value) || empty($value))
				? $value
				: $this->fromObject((array) $value);
		}

		return $output;
	}
}
