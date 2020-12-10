<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.1
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\Ion;

/**
 * A basic event handler
 */
class Event {
	private static array $eventMap = [];

	/**
	 * Subscribe to an event
	 *
	 * @param string $eventName
	 * @param callable $handler
	 */
	public static function on(string $eventName, callable $handler): void
	{
		if ( ! array_key_exists($eventName, static::$eventMap))
		{
			static::$eventMap[$eventName] = [];
		}

		static::$eventMap[$eventName][] = $handler;
	}

	/**
	 * Fire off an event
	 *
	 * @param string $eventName
	 * @param array $args
	 */
	public static function emit(string $eventName, array $args = []): void
	{
		// Call each subscriber with the provided arguments
		if (array_key_exists($eventName, static::$eventMap))
		{
			array_walk(static::$eventMap[$eventName], static fn ($fn) => $fn(...$args));
		}
	}
}