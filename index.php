<?php
/**
 * Hummingbird Anime Client
 *
 * An API client for Hummingbird to manage anime and manga watch lists
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren
 * @copyright   Copyright (c) 2015
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 * @license     MIT
 */
use Whoops\Handler\PrettyPageHandler;
use Whoops\Handler\JsonResponseHandler;

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

/**
 * Set up autoloaders
 *
 * @codeCoverageIgnore
 * @return void
 */
spl_autoload_register(function($class) {
	$class_parts = explode('\\', $class);
	$ns_path = SRC_DIR . '/' . implode('/', $class_parts) . ".php";

	if (file_exists($ns_path))
	{
		require_once($ns_path);
		return;
	}
});

require _dir(ROOT_DIR, '/vendor/autoload.php');
require _dir(SRC_DIR, '/functions.php');

// -------------------------------------------------------------------------
// Setup error handling
// -------------------------------------------------------------------------
$whoops = new \Whoops\Run();

// Set up default handler for general errors
$defaultHandler = new PrettyPageHandler();
$whoops->pushHandler($defaultHandler);

// Set up json handler for ajax errors
$jsonHandler = new JsonResponseHandler();
$jsonHandler->onlyForAjaxRequests(TRUE);
$whoops->pushHandler($jsonHandler);

// Register as the error handler
$whoops->register();

// -----------------------------------------------------------------------------
// Dependency Injection setup
// -----------------------------------------------------------------------------
require _dir(CONF_DIR, 'base_config.php'); // $base_config
require _dir(CONF_DIR, 'config.php'); // $config
$config_array = array_merge($base_config, $config);
$di = require _dir(APP_DIR, 'bootstrap.php');
$container = $di($config_array);
$container->set('error-handler', $defaultHandler);

// -----------------------------------------------------------------------------
// Dispatch to the current route
// -----------------------------------------------------------------------------
$container->get('dispatcher')->__invoke();

// End of index.php