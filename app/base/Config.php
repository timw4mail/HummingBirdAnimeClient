<?php

/**
 * Wrapper for configuration values
 */
class Config {

	/**
	 * Config object
	 *
	 * @var array
	 */
	protected $config;

	/**
	 * Constructor
	 *
	 * @param array $config_files
	 */
	public function __construct(Array $config_files=[])
	{
		// @codeCoverageIgnoreStart
		if (empty($config_files))
		{
			/* $config = */require_once _dir(CONF_DIR, 'config.php');
			/* $base_config = */require_once _dir(CONF_DIR, 'base_config.php');
		}
		// @codeCoverageIgnoreEnd
		else
		{
			$config = $config_files['config'];
			$base_config = $config_files['base_config'];
		}

		$this->config = $config;

		foreach($base_config as $key => $val)
		{
			$this->config[$key] = $val;
		}

	}

	/**
	 * Getter for config values
	 *
	 * @param string $key
	 * @return mixed
	 */
	public function __get($key)
	{
		if (isset($this->config[$key]))
		{
			return $this->config[$key];
		}

		return NULL;
	}
}
// End of config.php