<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.4
 *
 * @copyright   2015 - 2025  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.3
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\Ion;

use ReflectionClass;
use ReflectionException;

/**
 * Class emulating an enumeration type
 */
abstract class ConstList
{
	/**
	 * Return the list of constant values for the Enum
	 *
	 * @return array<string|int>
	 * @throws ReflectionException
	 */
	public static function getConstList(): array
	{
		static $self;

		if ($self === NULL)
		{
			$class = static::class;
			$self = new $class();
		}

		$reflect = new ReflectionClass($self);

		return $reflect->getConstants();
	}

	/**
	 * Verify that a constant value is valid
	 *
	 * @throws ReflectionException
	 */
	public static function isValid(mixed $key): bool
	{
		$values = array_values(static::getConstList());

		return in_array($key, $values, TRUE);
	}
}

// End of Enum.php
