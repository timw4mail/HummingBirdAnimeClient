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

namespace Aviat\Ion;

/**
 * A basic event handler
 */
class Event
{
	private static array $eventMap = [];

	/**
	 * Subscribe to an event
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
