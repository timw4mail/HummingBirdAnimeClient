<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshome.page>
 * @copyright   2015 - 2022  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\Ion;

use ReflectionClass;
use ReflectionException;

/**
 * Class emulating an enumeration type
 */
abstract class Enum
{
	/**
	 * Return the list of constant values for the Enum
	 *
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
