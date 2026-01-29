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

use InvalidArgumentException;

/**
 * Standard interface for retrieving/setting configuration values
 */
interface ConfigInterface
{
	/**
	 * Does the config item exist?
	 * @param array<string|int>|int|string $key
	 */
	public function has(array|int|string $key): bool;

	/**
	 * Get a config value
	 * @param array<string|int>|int|string|null $key
	 */
	public function get(array|int|string|null $key = NULL): mixed;

	/**
	 * Set a config value
	 *
	 * @param array<string|int>|int|string $key
	 * @throws InvalidArgumentException
	 */
	public function set(array|int|string $key, mixed $value): self;

	/**
	 * Remove a config value
	 * @param array<string|int>|int|string $key
	 */
	public function delete(array|int|string $key): void;
}
