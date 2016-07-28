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


use Aviat\Ion\Exception\ConfigException;

use InvalidArgumentException;

/**
 * Wrapper for configuration values
 */
class Config implements ConfigInterface {

	use ArrayWrapper;

	/**
	 * Config object
	 *
	 * @var \Aviat\Ion\Type\ArrayType
	 */
	protected $map = [];

	/**
	 * Constructor
	 *
	 * @param array $config_array
	 */
	public function __construct(array $config_array = [])
	{
		$this->map = $this->arr($config_array);
	}

	/**
	 * Get a config value
	 *
	 * @param array|string $key
	 * @return mixed
	 * @throws ConfigException
	 */
	public function get($key)
	{
		if (is_array($key))
		{
			return $this->map->get_deep_key($key);
		}

		return $this->map->get($key);
	}

	/**
	 * Remove a config value
	 *
	 * @param  string|array $key
	 * @return void
	 */
	public function delete($key)
	{
		if (is_array($key))
		{
			$this->map->set_deep_key($key, NULL);
		}
		else
		{
			$pos =& $this->map->get($key);
			$pos = NULL;
		}
	}

	/**
	 * Set a config value
	 *
	 * @param integer|string|array $key
	 * @param mixed $value
	 * @throws InvalidArgumentException
	 * @return Config
	 */
	public function set($key, $value)
	{
		if (is_array($key))
		{
			$this->map->set_deep_key($key, $value);
		}
		else if (is_scalar($key) && ! empty($key))
		{
			$this->map->set($key, $value);
		}
		else
		{
			throw new InvalidArgumentException("Key must be integer, string, or array, and cannot be empty");
		}

		return $this;
	}
}
// End of config.php