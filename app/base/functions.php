<?php

/**
 * Global functions
 */

/**
 * HTML selection helper function
 *
 * @param string $a - First item to compare
 * @param string $b - Second item to compare
 * @return string
 */
function is_selected($a, $b)
{
	return ($a === $b) ? 'selected' : '';
}

/**
 * Inverse of selected helper function
 *
 * @param string $a - First item to compare
 * @param string $b - Second item to compare
 * @return string
 */
function is_not_selected($a, $b)
{
	return ($a !== $b) ? 'selected' : '';
}

/**
 * Generate full url path from the route path based on config
 *
 * @param string $path - (optional) The route path
 * @param string $type - (optional) The controller (anime or manga), defaults to anime
 * @return string
 */
function full_url($path="", $type="anime")
{
	global $config;

	$config_path = $config->{"{$type}_path"};
	$config_host = $config->{"{$type}_host"};
	$config_default_route = $config->{"default_{$type}_path"};

	// Remove beginning/trailing slashes
	$config_path = trim($config_path, '/');
	$path = trim($path, '/');

	// Remove any optional parameters from the route
	$path = preg_replace('`{/.*?}`i', '', $path);

	// Set the default view
	if ($path === '')
	{
		$path .= trim($config_default_route, '/');
		if ($config->default_to_list_view) $path .= '/list';
	}

	// Set the appropriate HTTP host
	$host = ($config_host !== '') ? $config_host : $_SERVER['HTTP_HOST'];

	// Set an leading folder
	if ($config_path !== '')
	{
		$path = "{$config_path}/{$path}";
	}

	return "//{$host}/{$path}";
}

/**
 * Get the last segment of the current url
 *
 * @return string
 */
function last_segment()
{
	$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
	$segments = explode('/', $path);
	return end($segments);
}

// End of functions.php