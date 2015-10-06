<?php
/**
 * Base Configuration class
 */

namespace Aviat\AnimeClient;

/**
 * Wrapper for configuration values
 *
 * @property Database Config $database
 * @property Menu Config $menus
 */
class Config {

	/**
	 * Config object
	 *
	 * @var array
	 */
	protected $config = [];

	/**
	 * Constructor
	 *
	 * @param array $config_files
	 */
	public function __construct(array $config_files=[])
	{
		// @codeCoverageIgnoreStart
		if (empty($config_files))
		{
			require_once \_dir(CONF_DIR, 'config.php'); // $config
			require_once \_dir(CONF_DIR, 'base_config.php'); // $base_config

			$this->config = array_merge($config, $base_config);
		}
		else // @codeCoverageIgnoreEnd
		{
			$this->config = $config_files;
		}
	}

	/**
	 * Get a config value
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function get($key)
	{
		if (isset($this->config[$key]))
		{
			return $this->config[$key];
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
		$this->config[$key] = $value;
		return $this;
	}
}
// End of config.php