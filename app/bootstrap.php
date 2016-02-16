<?php

/**
 * Bootstrap / Dependency Injection
 */
namespace Aviat\AnimeClient;

use Aura\Html\HelperLocatorFactory;
use Aura\Router\RouterContainer;
use Aura\Session\SessionFactory;
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Handler\BrowserConsoleHandler;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Response;

use Aviat\Ion\Di\Container;
use Aviat\AnimeClient\Auth\HummingbirdAuth;
use Aviat\AnimeClient\Model;

// -----------------------------------------------------------------------------
// Setup DI container
// -----------------------------------------------------------------------------
return function(array $config_array = []) {
	$container = new Container();

	// -------------------------------------------------------------------------
	// Logging
	// -------------------------------------------------------------------------

	$app_logger = new Logger('animeclient');
	$app_logger->pushHandler(new RotatingFileHandler(__DIR__ . '/logs/app.log', Logger::NOTICE));
	$container->setLogger($app_logger, 'default');

	// -------------------------------------------------------------------------
	// Injected Objects
	// -------------------------------------------------------------------------

	// Create Config Object
	$config = new Config($config_array);
	$container->set('config', $config);

	// Create Aura Router Object
	$container->set('aura-router', new RouterContainer);

	// Create Html helper Object
	$html_helper = (new HelperLocatorFactory)->newInstance();
	$html_helper->set('menu', function() use ($container) {
		$menu_helper = new Helper\Menu();
		$menu_helper->setContainer($container);
		return $menu_helper;
	});
	$container->set('html-helper', $html_helper);

	// Create Request/Response Objects
	$request = ServerRequestFactory::fromGlobals(
		$_SERVER,
		$_GET,
		$_POST,
		$_COOKIE,
		$_FILES
	);
	$container->set('request', $request);
	$container->set('response', new Response());

	// Create session Object
	$session = (new SessionFactory())->newInstance($_COOKIE);
	$container->set('session', $session);

	$container->set('url-generator', new UrlGenerator($container));


	// Miscellaneous helper methods
	$anime_client = new AnimeClient();
	$anime_client->setContainer($container);
	$container->set('anime-client', $anime_client);

	// Models
	$container->set('api-model', new Model\API($container));
	$container->set('anime-model', new Model\Anime($container));
	$container->set('manga-model', new Model\Manga($container));
	$container->set('anime-collection-model', new Model\AnimeCollection($container));

	$container->set('auth', new HummingbirdAuth($container));

	// -------------------------------------------------------------------------
	// Dispatcher
	// -------------------------------------------------------------------------
	$container->set('dispatcher', new Dispatcher($container));

	return $container;
};

// End of bootstrap.php