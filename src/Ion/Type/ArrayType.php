<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @copyright   2015 - 2022  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshome.page/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\Ion\Type;

use InvalidArgumentException;
use function in_array;

/**
 * Wrapper class for native array methods for convenience
 *
 * @method array chunk(int $size, bool $preserve_keys = FALSE)
 * @method array filter(callable $callback = NULL, int $flag = 0)
 * @method array pluck(mixed $column_key, mixed $index_key = NULL)
 */
class ArrayType
{
	/**
	 * The current array
	 */
	protected array $arr = [];

	/**
	 * Map generated methods to their native implementations
	 */
	protected array $nativeMethods = [
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
	 */
	protected array $nativeInPlaceMethods = [
		'shuffle' => 'shuffle',
		'shift' => 'array_shift',
		'unshift' => 'array_unshift',
		'push' => 'array_push',
		'pop' => 'array_pop',
	];

	/**
	 * Create an ArrayType wrapper class
	 */
	private function __construct(array &$arr)
	{
		$this->arr =& $arr;
	}

	/**
	 * Call one of the dynamically created methods
	 *
	 * @throws InvalidArgumentException
	 */
	public function __call(string $method, array $args): mixed
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
	 * Create an ArrayType wrapper class from an array
	 */
	public static function from(array $arr): ArrayType
	{
		return new ArrayType($arr);
	}

	/**
	 * Does the passed key exist in the current array?
	 */
	public function hasKey(int|string|array $key): bool
	{
		if (\is_array($key))
		{
			$pos =& $this->arr;

			foreach ($key as $level)
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
	 * @return mixed[]
	 */
	public function fill(int $start_index, int $num, mixed $value): array
	{
		return array_fill($start_index, $num, $value);
	}

	/**
	 * Call a callback on each item of the array
	 *
	 * @return mixed[]
	 */
	public function map(callable $callback): array
	{
		return array_map($callback, $this->arr);
	}

	/**
	 * Find an array key by its associated value
	 */
	public function search(mixed $value, bool $strict = TRUE): int|string|false|null
	{
		return array_search($value, $this->arr, $strict);
	}

	/**
	 * Determine if the array has the passed value
	 */
	public function has(mixed $value, bool $strict = TRUE): bool
	{
		return in_array($value, $this->arr, $strict);
	}

	/**
	 * Return the array, or a key
	 */
	public function &get(string|int|null $key = NULL): mixed
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
	 */
	public function set(mixed $key, mixed $value): ArrayType
	{
		$this->arr[$key] = $value;

		return $this;
	}

	/**
	 * Return a reference to the value of an arbitrary key on the array
	 *
	 * @example $arr = ArrayType::from([0 => ['data' => ['foo' => 'bar']]]);
	 * $val = $arr->getDeepKey([0, 'data', 'foo']);
	 * // returns 'bar'
	 * @param array $key An array of keys of the array
	 */
	public function &getDeepKey(array $key): mixed
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
	 * @return mixed[]
	 */
	public function setDeepKey(array $key, mixed $value): array
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
