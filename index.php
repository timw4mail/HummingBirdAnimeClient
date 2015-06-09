<?php

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/app/base/autoloader.php';

session_start();

use \Whoops\Handler\PrettyPageHandler;
use \Whoops\Handler\JsonResponseHandler;

function is_selected($a, $b)
{
	return ($a === $b) ? 'selected' : '';
}

$config = require_once(__DIR__ . '/app/config/config.php');

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
//$defaultHandler->addDataTable('route', (array)$router->get_route());
$router->dispatch();

// End of index.php