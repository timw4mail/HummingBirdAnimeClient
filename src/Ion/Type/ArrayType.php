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

namespace Aviat\Ion\Type;

use InvalidArgumentException;

use function in_array;

/**
 * Wrapper class for native array methods for convenience
 *
 * @method array<mixed> chunk(int $size, bool $preserve_keys = FALSE)
 * @method array<mixed> filter(callable $callback = NULL, int $flag = 0)
 * @method array<mixed> pluck(mixed $column_key, mixed $index_key = NULL)
 */
class ArrayType
{
	/**
	 * The current array
	 * @var array<int|string, mixed>
	 */
	protected array $arr = [];

	/**
	 * Map generated methods to their native implementations
	 * @var array<string, string>
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
	 * @var array<string, string>
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
	 * @param array<int|string, mixed> $arr
	 */
	private function __construct(array &$arr)
	{
		$this->arr = &$arr;
	}

	/**
	 * Call one of the dynamically created methods
	 *
	 * @param array<int|string, mixed> $args
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
	 * @param array<int|string, mixed> $arr
	 */
	public static function from(array $arr): ArrayType
	{
		return new ArrayType($arr);
	}

	/**
	 * Does the passed key exist in the current array?
	 * @param int|string|array<string, mixed> $key
	 */
	public function hasKey(int|string|array $key): bool
	{
		if (\is_array($key))
		{
			$pos = &$this->arr;

			foreach ($key as $level)
			{
				if (! array_key_exists($level, $pos))
				{
					return false;
				}

				$pos = &$pos[$level];
			}

			return true;
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
	public function search(mixed $value, bool $strict = true): int|string|false|null
	{
		return array_search($value, $this->arr, $strict);
	}

	/**
	 * Determine if the array has the passed value
	 */
	public function has(mixed $value, bool $strict = true): bool
	{
		return in_array($value, $this->arr, $strict);
	}

	/**
	 * Return the array, or a key
	 */
	public function &get(string|int|null $key = null): mixed
	{
		$value = null;
		if ($key === null)
		{
			$value = &$this->arr;
		}
		else
		{
			if ($this->hasKey($key))
			{
				$value = &$this->arr[$key];
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
	 * @param array<int|string> $key An array of keys of the array
	 */
	public function &getDeepKey(array $key): mixed
	{
		$pos = &$this->arr;

		foreach ($key as $level)
		{
			if (empty($pos) || ! is_array($pos))
			{
				// Directly returning a NULL value here will
				// result in a reference error. This isn't
				// excess code, just what's required for this
				// unique situation.
				return null;
			}

			$pos = &$pos[$level];
		}

		return $pos;
	}

	/**
	 * Sets the value of an arbitrarily deep key in the array
	 * and returns the modified array
	 *
	 * @param array<int|string> $key An array of keys of the array
	 * @return mixed[]
	 */
	public function setDeepKey(array $key, mixed $value): array
	{
		$pos = &$this->arr;

		// Iterate through the levels of the array,
		// create the levels if they don't exist
		foreach ($key as $level)
		{
			if (! \is_array($pos) && empty($pos))
			{
				$pos = [];
				$pos[$level] = [];
			}

			$pos = &$pos[$level];
		}

		$pos = $value;

		return $this->arr;
	}
}

// End of ArrayType.php
