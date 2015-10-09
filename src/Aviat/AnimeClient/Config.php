<?php
/**
 * Base Configuration class
 */

namespace Aviat\AnimeClient;

/**
 * Wrapper for configuration values
 */
class Config {

	/**
	 * Config object
	 *
	 * @var array
	 */
	protected $map = [];

	/**
	 * Constructor
	 *
	 * @param array $config_files
	 */
	public function __construct(array $config_array = [])
	{
		$this->map = $config_array;
	}

	/**
	 * Get a config value
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get($key)
	{
		if (array_key_exists($key, $this->map))
		{
			return $this->map[$key];
		}

		return NULL;
	}

	/**
	 * Set a config value
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return Config
	 */
	public function set($key, $value)
	{
		$this->map[$key] = $value;
		return $this;
	}
}
// End of config.php