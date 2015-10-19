<?php
/**
 * Global setup for unit tests
 */
 
// -----------------------------------------------------------------------------
// Autoloaders
// -----------------------------------------------------------------------------

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

// Define base path constants
define('ROOT_DIR', realpath(_dir(__DIR__, "/../")));
define('APP_DIR', _dir(ROOT_DIR, 'app'));
define('CONF_DIR', _dir(APP_DIR, 'config'));
define('SRC_DIR', _dir(ROOT_DIR, 'src'));
define('BASE_DIR', _dir(SRC_DIR, 'Base'));
define('TEST_DATA_DIR', _dir(__DIR__, 'test_data'));
define('TEST_VIEW_DIR', _dir(__DIR__, 'test_views'));
require _dir(ROOT_DIR, '/vendor/autoload.php');
require _dir(SRC_DIR, '/functions.php');

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
 
// -----------------------------------------------------------------------------
// Ini Settings
// -----------------------------------------------------------------------------
ini_set('session.use_cookies', 0);
ini_set("session.use_only_cookies",0);
ini_set("session.use_trans_sid",1);
// Start session here to supress error about headers not sent
session_start(); 

// -----------------------------------------------------------------------------
// Load base test case and mocks
// -----------------------------------------------------------------------------

// Pre-define some superglobals
$_SESSION = [];
$_COOKIE = [];

// Request base test case and mocks
require _dir(__DIR__, 'TestSessionHandler.php');
require _dir(__DIR__, 'mocks.php');
require _dir(__DIR__, 'AnimeClient_TestCase.php');

// End of bootstrap.php