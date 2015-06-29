<?php

namespace AnimeClient;

use \Whoops\Handler\PrettyPageHandler;
use \Whoops\Handler\JsonResponseHandler;
use \Aura\Web\WebFactory;
use \Aura\Router\RouterFactory;
use \GuzzleHttp\Client;
use \GuzzleHttp\Cookie\CookieJar;

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
// Injected Objects
// -----------------------------------------------------------------------------

// Create Config Object
$config = new Config();
require _dir(BASE_DIR, '/functions.php');

// Create Aura Router Object
$router_factory = new RouterFactory();
$aura_router = $router_factory->newInstance();

// Create Request/Response Objects
$web_factory = new WebFactory([
	'_GET' => $_GET,
	'_POST' => $_POST,
	'_COOKIE' => $_COOKIE,
	'_SERVER' => $_SERVER,
	'_FILES' => $_FILES
]);
$request = $web_factory->newRequest();
$response = $web_factory->newResponse();

// -----------------------------------------------------------------------------
// Router
// -----------------------------------------------------------------------------
$router = new Router($config, $aura_router, $request, $response);
$router->dispatch();

// End of bootstrap.php