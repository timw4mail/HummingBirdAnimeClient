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

namespace Aviat\Ion\Type;

/**
 * Wrapper class for native array methods for convenience
 *
 * @method array chunk(int $size, bool $preserve_keys = FALSE)
 * @method array pluck(mixed $column_key, mixed $index_key = NULL)
 * @method array filter(callable $callback = NULL, int $flag = 0)
 */
class ArrayType {

	/**
	 * The current array
	 *
	 * @var array
	 */
	protected $arr;

	/**
	 * Map generated methods to their native implementations
	 *
	 * @var array
	 */
	protected $native_methods = [
		'chunk' => 'array_chunk',
		'pluck' => 'array_column',
		'key_diff' => 'array_diff_key',
		'diff' => 'array_diff',
		'filter' => 'array_filter',
		'flip' => 'array_flip',
		'intersect' => 'array_intersect',
		'keys' => 'array_keys',
		'merge' => 'array_merge',
		'pad' => 'array_pad',
		'product' => 'array_product',
		'random' => 'array_rand',
		'reduce' => 'array_reduce',
		'reverse' => 'array_reverse',
		'sum' => 'array_sum',
		'unique' => 'array_unique',
		'values' => 'array_values',
	];

	/**
	 * Native methods that modify the passed in array
	 *
	 * @var array
	 */
	protected $native_in_place_methods = [
		'shuffle' => 'shuffle',
		'shift' => 'array_shift',
		'unshift' => 'array_unshift',
		'push' => 'array_push',
		'pop' => 'array_pop',
	];

	/**
	 * Create an ArrayType wrapper class
	 *
	 * @param array $arr
	 */
	public function __construct(array &$arr)
	{
		$this->arr =& $arr;
	}

	/**
	 * Call one of the dynamically created methods
	 *
	 * @param string $method
	 * @param array $args
	 * @return mixed
	 * @throws \InvalidArgumentException
	 */
	public function __call($method, $args)
	{
		// Simple mapping for the majority of methods
		if (array_key_exists($method, $this->native_methods))
		{
			$func = $this->native_methods[$method];
			// Set the current array as the first argument of the method
			array_unshift($args, $this->arr);
			return call_user_func_array($func, $args);
		}

		// Mapping for in-place methods
		if (array_key_exists($method, $this->native_in_place_methods))
		{
			$func = $this->native_in_place_methods[$method];
			$func($this->arr);
			return $this->arr;
		}

		throw new \InvalidArgumentException("Method '{$method}' does not exist");
	}

	/**
	 * Does the passed key exist in the current array?
	 *
	 * @param string $key
	 * @return bool
	 */
	public function has_key($key)
	{
		return array_key_exists($key, $this->arr);
	}

	/**
	 * Fill an array with the specified value
	 *
	 * @param int $start_index
	 * @param int $num
	 * @param mixed $value
	 * @return array
	 */
	public function fill($start_index, $num, $value)
	{
		return array_fill($start_index, $num, $value);
	}

	/**
	 * Call a callback on each item of the array
	 *
	 * @param callable $callback
	 * @return array
	 */
	public function map(callable $callback)
	{
		return array_map($callback, $this->arr);
	}

	/**
	 * Find an array key by its associated value
	 *
	 * @param mixed $value
	 * @param bool $strict
	 * @return false|integer|string
	 */
	public function search($value, $strict = FALSE)
	{
		return array_search($value, $this->arr, $strict);
	}

	/**
	 * Determine if the array has the passed value
	 *
	 * @param mixed $value
	 * @param bool $strict
	 * @return bool
	 */
	public function has($value, $strict = FALSE)
	{
		return in_array($value, $this->arr, $strict);
	}

	/**
	 * Return the array, or a key
	 *
	 * @param string|integer|null $key
	 * @return mixed
	 */
	public function &get($key = NULL)
	{
		$value = NULL;
		if (is_null($key))
		{
			$value =& $this->arr;
		}
		else
		{
			if ($this->has_key($key))
			{
				$value =& $this->arr[$key];
			}
		}

		return $value;
	}

	/**
	 * Set a key on the array
	 *
	 * @param mixed $key
	 * @param mixed $value
	 * @return ArrayType
	 */
	public function set($key, $value)
	{
		$this->arr[$key] = $value;
		return $this;
	}

	/**
	 * Return a reference to the value of an arbitrary key on the array
	 *
	 * @param  array $key
	 * @return mixed
	 */
	public function &get_deep_key(array $key)
	{
		$pos =& $this->arr;

		foreach ($key as $level)
		{
			if (empty($pos) || ! is_array($pos))
			{
				// Directly returning a NULL value here will
				// result in a reference error. This isn't
				// excess code, just what's required for this
				// unique situation.
				$pos = NULL;
				return $pos;
			}
			$pos =& $pos[$level];
		}

		return $pos;
	}

	/**
	 * Sets the value of an arbitrarily deep key in the array
	 * and returns the modified array
	 *
	 * @param array  $key
	 * @param mixed $value
	 * @return array
	 */
	public function set_deep_key(array $key, $value)
	{
		$pos =& $this->arr;

		// Iterate through the levels of the array,
		// create the levels if they don't exist
		foreach ($key as $level)
		{
			if ( ! is_array($pos) && empty($pos))
			{
				$pos = [];
				$pos[$level] = [];
			}
			$pos =& $pos[$level];
		}

		$pos = $value;

		return $this->arr;
	}
}
// End of ArrayType.php