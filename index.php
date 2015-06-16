<?php
/**
 * Here begins everything!
 */

/**
 * Well, whose list is it?
 */
define('WHOSE', "Tim's");

/**
 * Joins paths together. Variadic to take an
 * arbitrary number of arguments
 *
 * @return string
 */
function _dir() { return implode(DIRECTORY_SEPARATOR, func_get_args()); }

define('ROOT_DIR', __DIR__);
define('APP_DIR', _dir(ROOT_DIR, 'app'));
define('CONF_DIR', _dir(APP_DIR, 'config'));
define('BASE_DIR', _dir(APP_DIR, 'base'));

// Load config and global functions
$config = require _dir(APP_DIR, '/config/config.php');
require _dir(BASE_DIR, '/functions.php');

// Setup autoloaders
require _dir(ROOT_DIR, '/vendor/autoload.php');
spl_autoload_register(function ($class) {
	$dirs = ["base", "controllers", "models"];

	foreach($dirs as $dir)
	{
		$file = _dir(APP_DIR, $dir, "{$class}.php");
		if (file_exists($file))
		{
			require_once $file;
			return;
		}
	}
});

session_start();

use \Whoops\Handler\PrettyPageHandler;
use \Whoops\Handler\JsonResponseHandler;

// -----------------------------------------------------------------------------
// Setup error handling
// -----------------------------------------------------------------------------
$whoops = new \Whoops\Run();

// Set up default handler for general errors
$defaultHandler = new PrettyPageHandler();
$whoops->pushHandler($defaultHandler);

// Set up json handler for ajax errors
$jsonHandler = new JsonResponseHandler();
$jsonHandler->onlyForAjaxRequests(true);
$whoops->pushHandler($jsonHandler);

$whoops->register();

// -----------------------------------------------------------------------------
// Router
// -----------------------------------------------------------------------------

$router = new Router();
$defaultHandler->addDataTable('route', (array)$router->get_route());
$router->dispatch();

// End of index.php