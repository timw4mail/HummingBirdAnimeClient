<?php
/**
 * Hummingbird Anime Client
 *
 * An API client for Hummingbird to manage anime and manga watch lists
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren
 * @copyright   Copyright (c) 2015 - 2016
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

// Define base directories
$APP_DIR = _dir(__DIR__, 'app');
$SRC_DIR = _dir(__DIR__, 'src');
$CONF_DIR = _dir($APP_DIR, 'config');

/**
 * Set up autoloaders
 *
 * @codeCoverageIgnore
 * @return void
 */
spl_autoload_register(function($class) use ($SRC_DIR) {
	$class_parts = explode('\\', $class);
	$ns_path = $SRC_DIR . '/' . implode('/', $class_parts) . ".php";

	if (file_exists($ns_path))
	{
		require_once($ns_path);
		return;
	}
});

require _dir(__DIR__, '/vendor/autoload.php');

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
require _dir($CONF_DIR, 'base_config.php'); // $base_config
require _dir($CONF_DIR, 'config.php'); // $config
$config_array = array_merge($base_config, $config);
$di = require _dir($APP_DIR, 'bootstrap.php');

// Unset 'constants'
unset($APP_DIR);
unset($SRC_DIR);
unset($CONF_DIR);

$container = $di($config_array);

// -----------------------------------------------------------------------------
// Dispatch to the current route
// -----------------------------------------------------------------------------
$container->get('dispatcher')->__invoke();

// End of index.php