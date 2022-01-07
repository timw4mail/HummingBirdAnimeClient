<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2021  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\Ion;

/**
 * Standard interface for retrieving/setting configuration values
 */
interface ConfigInterface {
	/**
	 * Does the config item exist?
	 *
	 * @param array|int|string $key
	 * @return bool
	 */
	public function has(array|int|string $key): bool;

	/**
	 * Get a config value
	 *
	 * @param array|string|null $key
	 * @return mixed
	 */
	public function get(array|string $key = NULL): mixed;

	/**
	 * Set a config value
	 *
	 * @param array|integer|string $key
	 * @param mixed                $value
	 * @return ConfigInterface
	 * @throws \InvalidArgumentException
	 */
	public function set(array|int|string $key, mixed $value): self;

	/**
	 * Remove a config value
	 *
	 * @param array|string $key
	 * @return void
	 */
	public function delete(array|string $key): void;
}