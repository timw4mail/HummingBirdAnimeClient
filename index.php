<?php
/**
 * Here begins everything!
 */

namespace AnimeClient;

// -----------------------------------------------------------------------------
// ! Start config
// -----------------------------------------------------------------------------

/**
 * Well, whose list is it?
 */
define('WHOSE', "Tim's");

// -----------------------------------------------------------------------------
// ! End config
// -----------------------------------------------------------------------------

// Work around the silly timezone error
$timezone = ini_get('date.timezone');
if ($timezone === '' || $timezone === FALSE)
{
	ini_set('date.timezone', 'GMT');
}

define('ROOT_DIR', __DIR__);
define('APP_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . 'app');
define('CONF_DIR', APP_DIR . DIRECTORY_SEPARATOR . 'config');
define('BASE_DIR', APP_DIR . DIRECTORY_SEPARATOR . 'base');
require BASE_DIR . DIRECTORY_SEPARATOR . 'pre_conf_functions.php';

// Setup autoloaders
_setup_autoloaders();

// Load config and global functions
$config = new Config();
require _dir(BASE_DIR, '/functions.php');

\session_start();

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