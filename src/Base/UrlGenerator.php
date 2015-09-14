<?php

namespace AnimeClient\Base;

/**
 * UrlGenerator class.
 */
class UrlGenerator extends RoutingBase {

	/**
	 * Get the base url for css/js/images
	 *
	 * @return string
	 */
	public function asset_url(/*...*/)
	{
		$args = func_get_args();
		$base_url = rtrim($this->url(""), '/');

		$base_url = "{$base_url}" . $this->__get("asset_path");

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

	/**
	 * Full default path for the list pages
	 *
	 * @param string $type
	 * @return string
	 */
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
// End of UrlGenerator.php