<?php

/**
 * Global functions
 */

/**
 * Check if the user is currently logged in
 *
 * @return bool
 */
function is_logged_in()
{
	return array_key_exists('hummingbird_anime_token', $_SESSION);
}

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

/**
 * Determine whether to show the sub-menu
 *
 * @return bool
 */
function is_view_page()
{
	$blacklist = ['edit', 'add', 'update', 'login', 'logout'];
	$page_segments = explode("/", $_SERVER['REQUEST_URI']);

	$intersect = array_intersect($page_segments, $blacklist);

	return empty($intersect);
}

// End of functions.php