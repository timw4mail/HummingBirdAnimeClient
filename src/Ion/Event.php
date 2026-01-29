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

namespace Aviat\Ion;

use Aviat\Ion\Type\EventType;

/**
 * A basic event handler
 */
class Event
{
	/**
	 * @var array<EventType, callable|list<callable>>
	 */
	protected static array $eventMap = [];

	/**
	 * Subscribe to an event
	 */
	public static function on(EventType $eventName, callable $handler): void
	{
		if ( ! array_key_exists($eventName->value, static::$eventMap))
		{
			static::$eventMap[$eventName->value] = [];
		}

		static::$eventMap[$eventName->value][] = $handler;
	}

	/**
	 * Fire off an event
	 * @param array<mixed> $args
	 */
	public static function emit(EventType $eventName, array $args = []): void
	{
		if ( ! array_key_exists($eventName->value, static::$eventMap))
		{
			return;
		}

		// Call each subscriber with the provided arguments
		array_walk(
			static::$eventMap[$eventName->value],
			static fn (callable $fn) => $fn(...$args)
		);
	}
}
