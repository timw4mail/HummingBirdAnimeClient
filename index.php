<?php
/**
 * Here begins everything!
 */

session_start();

// Work around the silly timezone error
$timezone = ini_get('date.timezone');
if ($timezone === '' || $timezone === FALSE)
{
	ini_set('date.timezone', 'GMT');
}

// Define base directories
define('ROOT_DIR', __DIR__);
define('APP_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . 'app');
define('SRC_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . 'src');
define('CONF_DIR', APP_DIR . DIRECTORY_SEPARATOR . 'config');
define('BASE_DIR', SRC_DIR . DIRECTORY_SEPARATOR . 'Base');

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

// Set up composer autoloader
require _dir(ROOT_DIR, '/vendor/autoload.php');

/**
 * Set up autoloaders
 *
 * @codeCoverageIgnore
 * @return void
 */
spl_autoload_register(function ($class) {
	$class_parts = explode('\\', $class);
	$ns_path = SRC_DIR . '/' . implode('/', $class_parts) . ".php";

	if (file_exists($ns_path))
	{
		require_once($ns_path);
		return;
	}
});

// Do dependency injection, and go!
require _dir(APP_DIR, 'bootstrap.php');

// End of index.php