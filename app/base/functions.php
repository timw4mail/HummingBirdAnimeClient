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
 * Generate full url path from the route path based on config
 *
 * @param string $path - The route path
 * @param [string] $host - The controller (anime or manga), defaults to anime
 * @return string
 */
function full_url($path, $type="anime")
{
	global $config;

	$config_path = $config->{"{$type}_path"};
	$config_host = $config->{"{$type}_host"};

	// Remove beginning/trailing slashes
	$config_path = trim($config_path, '/');
	$path = trim($path, '/');

	$host = ($config_host !== '') ? $config_host : $_SERVER['HTTP_HOST'];

	if ($config_path !== '')
	{
		$path = "{$config_path}/{$path}";
	}

	return "//{$host}/{$path}";
}

// End of functions.php