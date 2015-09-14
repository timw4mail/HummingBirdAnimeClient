<?php

/**
 * Functions that need to be included before config
 */

/**
 * Joins paths together. Variadic to take an
 * arbitrary number of arguments
 *
 * @return string
 */
function _dir()
{
	return implode(DIRECTORY_SEPARATOR, func_get_args());
}

/**
 * Set up autoloaders
 *
 * @codeCoverageIgnore
 * @return void
 */
function _setup_autoloaders()
{
	require _dir(ROOT_DIR, '/vendor/autoload.php');
	spl_autoload_register(function ($class) {
		$class_parts = explode('\\', $class);
		array_shift($class_parts);
		$ns_path = APP_DIR . '/' . implode('/', $class_parts) . ".php";

		if (file_exists($ns_path))
		{
			require_once($ns_path);
			return;
		}
	});
}