<?php declare(strict_types=1);
/**
 * Ion
 *
 * Building blocks for web development
 *
 * PHP version 7.2
 *
 * @package     Ion
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2019 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     3.0.0
 * @link        https://git.timshomepage.net/aviat/ion
 */

namespace Aviat\Ion\Type;

use InvalidArgumentException;

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
	protected $nativeMethods = [
		'chunk' => 'array_chunk',
		'diff' => 'array_diff',
		'filter' => 'array_filter',
		'flip' => 'array_flip',
		'intersect' => 'array_intersect',
		'key_diff' => 'array_diff_key',
		'keys' => 'array_keys',
		'merge' => 'array_merge',
		'pad' => 'array_pad',
		'pluck' => 'array_column',
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
	protected $nativeInPlaceMethods = [
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
	 * @param array  $args
	 * @return mixed
	 * @throws InvalidArgumentException
	 */
	public function __call(string $method, array $args)
	{
		// Simple mapping for the majority of methods
		if (array_key_exists($method, $this->nativeMethods))
		{
			$func = $this->nativeMethods[$method];
			// Set the current array as the first argument of the method
			return $func($this->arr, ...$args);
		}

		// Mapping for in-place methods
		if (array_key_exists($method, $this->nativeInPlaceMethods))
		{
			$func = $this->nativeInPlaceMethods[$method];
			$func($this->arr);
			return $this->arr;
		}

		throw new InvalidArgumentException("Method '{$method}' does not exist");
	}

	/**
	 * Does the passed key exist in the current array?
	 *
	 * @param int|string|array $key
	 * @return bool
	 */
	public function hasKey($key): bool
	{
		if (\is_array($key))
		{
			$pos =& $this->arr;

			foreach($key as $level)
			{
				if ( ! array_key_exists($level, $pos))
				{
					return FALSE;
				}

				$pos =& $pos[$level];
			}

			return TRUE;
		}

		return array_key_exists($key, $this->arr);
	}

	/**
	 * Fill an array with the specified value
	 *
	 * @param int   $start_index
	 * @param int   $num
	 * @param mixed $value
	 * @return array
	 */
	public function fill(int $start_index, int $num, $value): array
	{
		return array_fill($start_index, $num, $value);
	}

	/**
	 * Call a callback on each item of the array
	 *
	 * @param callable $callback
	 * @return array
	 */
	public function map(callable $callback): array
	{
		return array_map($callback, $this->arr);
	}

	/**
	 * Find an array key by its associated value
	 *
	 * @param mixed $value
	 * @param bool  $strict
	 * @return false|integer|string
	 */
	public function search($value, bool $strict = TRUE)
	{
		return array_search($value, $this->arr, $strict);
	}

	/**
	 * Determine if the array has the passed value
	 *
	 * @param mixed $value
	 * @param bool  $strict
	 * @return bool
	 */
	public function has($value, bool $strict = TRUE): bool
	{
		return \in_array($value, $this->arr, $strict);
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
		if ($key === NULL)
		{
			$value =& $this->arr;
		}
		else
		{
			if ($this->hasKey($key))
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
	public function set($key, $value): ArrayType
	{
		$this->arr[$key] = $value;
		return $this;
	}

	/**
	 * Return a reference to the value of an arbitrary key on the array
	 *
	 * @example $arr = new ArrayType([0 => ['data' => ['foo' => 'bar']]]);
	 * $val = $arr->getDeepKey([0, 'data', 'foo']);
	 * // returns 'bar'
	 * @param  array $key An array of keys of the array
	 * @return mixed
	 */
	public function &getDeepKey(array $key)
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
	 * @param array $key
	 * @param mixed $value
	 * @return array
	 */
	public function setDeepKey(array $key, $value): array
	{
		$pos =& $this->arr;

		// Iterate through the levels of the array,
		// create the levels if they don't exist
		foreach ($key as $level)
		{
			if ( ! \is_array($pos) && empty($pos))
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