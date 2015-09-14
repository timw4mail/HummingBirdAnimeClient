<?php
/**
 * Bootstrap / Dependency Injection
 */

namespace AnimeClient;

use \Whoops\Handler\PrettyPageHandler;
use \Whoops\Handler\JsonResponseHandler;
use \Aura\Web\WebFactory;
use \Aura\Router\RouterFactory;
use \Aura\Di\Container as DiContainer;
use \Aura\Di\Factory as DiFactory;

require _dir(SRC_DIR, '/functions.php');

// -----------------------------------------------------------------------------
// Setup DI container
// -----------------------------------------------------------------------------
$container = new Base\Container();

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

$container->set('error-handler', $defaultHandler);

// -----------------------------------------------------------------------------
// Injected Objects
// -----------------------------------------------------------------------------

// Create Config Object
$config = new Base\Config();
$container->set('config', $config);

// Create Aura Router Object
$router_factory = new RouterFactory();
$aura_router = $router_factory->newInstance();
$container->set('aura-router', $aura_router);

// Create Request/Response Objects
$web_factory = new WebFactory([
	'_GET' => $_GET,
	'_POST' => $_POST,
	'_COOKIE' => $_COOKIE,
	'_SERVER' => $_SERVER,
	'_FILES' => $_FILES
]);
$container->set('request', $web_factory->newRequest());
$container->set('response', $web_factory->newResponse());

// -----------------------------------------------------------------------------
// Router
// -----------------------------------------------------------------------------
$container->set('url-generator', new Base\UrlGenerator($container));

$router = new Base\Router($container);
$router->dispatch();

// End of bootstrap.php