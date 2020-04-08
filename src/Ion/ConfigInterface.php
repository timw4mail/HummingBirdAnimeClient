<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.3
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.2
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
	 * @param string|int|array $key
	 * @return bool
	 */
	public function has($key): bool;

	/**
	 * Get a config value
	 *
	 * @param array|string|null $key
	 * @return mixed
	 */
	public function get($key = NULL);

	/**
	 * Set a config value
	 *
	 * @param integer|string|array $key
	 * @param mixed                $value
	 * @throws \InvalidArgumentException
	 * @return ConfigInterface
	 */
	public function set($key, $value): self;

	/**
	 * Remove a config value
	 *
	 * @param  string|array $key
	 * @return void
	 */
	public function delete($key): void;
}