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
