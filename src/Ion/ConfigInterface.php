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

use InvalidArgumentException;

/**
 * Standard interface for retrieving/setting configuration values
 */
interface ConfigInterface
{
	/**
	 * Does the config item exist?
	 */
	public function has(array|int|string $key): bool;

	/**
	 * Get a config value
	 */
	public function get(array|string $key = NULL): mixed;

	/**
	 * Set a config value
	 *
	 * @throws InvalidArgumentException
	 */
	public function set(array|int|string $key, mixed $value): self;

	/**
	 * Remove a config value
	 */
	public function delete(array|string $key): void;
}
