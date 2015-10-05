<?php
/**
 * Bootstrap / Dependency Injection
 */

namespace Aviat\AnimeClient;

use \Whoops\Handler\PrettyPageHandler;
use \Whoops\Handler\JsonResponseHandler;
use Aura\Html\HelperLocatorFactory;
use \Aura\Web\WebFactory;
use \Aura\Router\RouterFactory;
use \Aura\Session\SessionFactory;

use Aviat\Ion\Di\Container;

require _dir(SRC_DIR, '/functions.php');

// -----------------------------------------------------------------------------
// Setup DI container
// -----------------------------------------------------------------------------
$di = function() {
	$container = new Container();

	// -------------------------------------------------------------------------
	// Setup error handling
	// -------------------------------------------------------------------------
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

	// -------------------------------------------------------------------------
	// Injected Objects
	// -------------------------------------------------------------------------

	// Create Config Object
	$config = new Config();
	$container->set('config', $config);

	// Create Aura Router Object
	$aura_router = (new RouterFactory())->newInstance();
	$container->set('aura-router', $aura_router);

	// Create Html helper Object
	$html_helper = (new HelperLocatorFactory)->newInstance();
	$html_helper->set('menu', function() use ($container) {
		$menu_helper = new Helper\Menu();
		$menu_helper->setContainer($container);
		return $menu_helper;
	});
	$container->set('html-helper', $html_helper);

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

	// Create session Object
	$session = (new SessionFactory())->newInstance($_COOKIE);
	$container->set('session', $session);

	$container->set('url-generator', new UrlGenerator($container));

	// -------------------------------------------------------------------------
	// Router
	// -------------------------------------------------------------------------
	$router = new Router($container);
	$container->set('router', $router);

	return $container;
};

$di()->get('router')->dispatch();

// End of bootstrap.php