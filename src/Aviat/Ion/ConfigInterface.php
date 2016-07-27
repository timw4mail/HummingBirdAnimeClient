<?php
/**
 * Ion
 *
 * Building blocks for web development
 *
 * @package     Ion
 * @author      Timothy J. Warren
 * @copyright   Copyright (c) 2015 - 2016
 * @license     MIT
 */

namespace Aviat\Ion;

interface ConfigInterface {
	/**
	 * Get a config value
	 *
	 * @param array|string $key
	 * @return mixed
	 */
	public function get($key);

	/**
	 * Set a config value
	 *
	 * @param integer|string|array $key
	 * @param mixed $value
	 * @throws \InvalidArgumentException
	 * @return ConfigInterface
	 */
	public function set($key, $value);

	/**
	 * Remove a config value
	 *
	 * @param  string|array $key
	 * @return void
	 */
	public function delete($key);
}