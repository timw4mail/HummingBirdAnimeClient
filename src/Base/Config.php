<?php
/**
 * Base Configuration class
 */

namespace AnimeClient\Base;

/**
 * Wrapper for configuration values
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
	public function __construct(Array $config_files=[])
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

	/**
	 * Get the base url for css/js/images
	 *
	 * @return string
	 */
	public function asset_url(/*...*/)
	{
		$args = func_get_args();
		$base_url = rtrim($this->url(""), '/');

		$routing_config = $this->__get("routing");


		$base_url = "{$base_url}" . $routing_config['asset_path'];

		array_unshift($args, $base_url);

		return implode("/", $args);
	}

	/**
	 * Get the base url from the config
	 *
	 * @param string $type - (optional) The controller
	 * @return string
	 */
	public function base_url($type="anime")
	{
		$config_path = trim($this->__get("{$type}_path"), "/");

		// Set the appropriate HTTP host
		$host = $_SERVER['HTTP_HOST'];
		$path = ($config_path !== '') ? $config_path : "";

		return implode("/", ['/', $host, $path]);
	}

	/**
	 * Generate a proper url from the path
	 *
	 * @param string $path
	 * @return string
	 */
	public function url($path)
	{
		$path = trim($path, '/');

		// Remove any optional parameters from the route
		$path = preg_replace('`{/.*?}`i', '', $path);

		// Set the appropriate HTTP host
		$host = $_SERVER['HTTP_HOST'];

		return "//{$host}/{$path}";
	}

	public function default_url($type)
	{
		$type = trim($type);
		$default_path = $this->__get("default_{$type}_path");

		if ( ! is_null($default_path))
		{
			return $this->url($default_path);
		}

		return "";
	}

	/**
	 * Generate full url path from the route path based on config
	 *
	 * @param string $path - (optional) The route path
	 * @param string $type - (optional) The controller (anime or manga), defaults to anime
	 * @return string
	 */
	public function full_url($path="", $type="anime")
	{
		$config_path = trim($this->__get("{$type}_path"), "/");
		$config_default_route = $this->__get("default_{$type}_path");

		// Remove beginning/trailing slashes
		$config_path = trim($config_path, '/');
		$path = trim($path, '/');

		// Remove any optional parameters from the route
		$path = preg_replace('`{/.*?}`i', '', $path);

		// Set the appropriate HTTP host
		$host = $_SERVER['HTTP_HOST'];

		// Set the default view
		if ($path === '')
		{
			$path .= trim($config_default_route, '/');
			if ($this->__get('default_to_list_view')) $path .= '/list';
		}

		// Set an leading folder
		/*if ($config_path !== '')
		{
			$path = "{$config_path}/{$path}";
		}*/

		return "//{$host}/{$path}";
	}
}
// End of config.php