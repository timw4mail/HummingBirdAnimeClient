<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.1
 *
 * @copyright   2015 - 2023  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Types;

use ArrayAccess;
use Countable;
use Stringable;

abstract class AbstractType implements ArrayAccess, Countable, Stringable
{
	/**
	 * Sets the properties by using the constructor
	 */
	final private function __construct(mixed $data = [])
	{
		$typeKeys = array_keys((array) $this);
		$dataKeys = array_keys((array) $data);

		$unsetKeys = array_diff($typeKeys, $dataKeys);

		foreach ($data as $key => $value)
		{
			$this->__set($key, $value);
		}

		// Remove unset keys so that they aren't serialized
		foreach ($unsetKeys as $k)
		{
			unset($this->{$k});
		}
	}

	/**
	 * Populate values for un-serializing data
	 */
	public static function __set_state(mixed $properties): self
	{
		return new static($properties);
	}

	/**
	 * See if a property is set
	 */
	final public function __isset(string $name): bool
	{
		return property_exists($this, $name) && isset($this->{$name});
	}

	/**
	 * Set a property on the type object
	 */
	final public function __set(string $name, mixed $value): void
	{
		$setterMethod = 'set' . ucfirst($name);

		if (method_exists($this, $setterMethod))
		{
			$this->{$setterMethod}($value);

			return;
		}

		if ( ! property_exists($this, $name))
		{
			$existing = json_encode($this, JSON_THROW_ON_ERROR);

			throw new UndefinedPropertyException("Trying to set undefined property: '{$name}'. Existing properties: {$existing}");
		}

		$this->{$name} = $value;
	}

	/**
	 * Get a property from the type object
	 */
	final public function __get(string $name): mixed
	{
		// Be a bit more lenient here, so that you can easily typecast missing
		// values to reasonable defaults, and not have to resort to array indexes
		return ($this->__isset($name)) ? $this->{$name} : NULL;
	}

	/**
	 * Create a string representation of the object for debugging
	 */
	public function __toString(): string
	{
		return print_r($this, TRUE);
	}

	/**
	 * Check the shape of the object, and return the array equivalent
	 */
	final public static function check(array $data = []): ?array
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
	 */
	final public static function from(mixed $data): static
	{
		return new static($data);
	}

	/**
	 * Implementing ArrayAccess
	 */
	final public function offsetExists(mixed $offset): bool
	{
		return $this->__isset((string) $offset);
	}

	/**
	 * Implementing ArrayAccess
	 */
	final public function offsetGet(mixed $offset): mixed
	{
		return $this->__get((string) $offset);
	}

	/**
	 * Implementing ArrayAccess
	 */
	final public function offsetSet(mixed $offset, mixed $value): void
	{
		$this->__set((string) $offset, $value);
	}

	/**
	 * Implementing ArrayAccess
	 */
	final public function offsetUnset(mixed $offset): void
	{
		if ($this->offsetExists($offset))
		{
			$strOffset = (string) $offset;
			unset($this->{$strOffset});
		}
	}

	/**
	 * Implementing Countable
	 */
	final public function count(): int
	{
		$keys = array_keys($this->toArray());

		return count($keys);
	}

	/**
	 * Recursively cast properties to an array
	 *
	 * Returns early on primitive values to work recursively.
	 *
	 * @param mixed $parent
	 */
	final public function toArray(mixed $parent = NULL): array
	{
		$fromObject = $this->fromObject($parent);

		return (is_array($fromObject)) ? $fromObject : [];
	}

	/**
	 * Determine whether the type has any properties set
	 */
	final public function isEmpty(): bool
	{
		$self = $this->toArray();

		foreach ($self as $value)
		{
			if ( ! empty($value))
			{
				return FALSE;
			}
		}

		return TRUE;
	}

	#[\PHPUnit\Framework\Attributes\CodeCoverageIgnore]
 final protected function fromObject(mixed $parent = NULL): float|null|bool|int|array|string
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
