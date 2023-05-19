<?php declare(strict_types=1);

use Aura\Html\HelperLocatorFactory;
use Aura\Session\SessionFactory;
use Aviat\Ion\Config;
use Aviat\Ion\Di\Container;
use Laminas\Diactoros\{Response, ServerRequestFactory};

// -----------------------------------------------------------------------------
// Setup DI container
// -----------------------------------------------------------------------------
return static function (array $config_array = []) {
	$container = new Container();

	$container->set('config', static fn () => new Config([]));

	$container->setInstance('config', new Config($config_array));

	$container->set('request', static fn() => ServerRequestFactory::fromGlobals(
			$GLOBALS['_SERVER'],
			$_GET,
			$_POST,
			$_COOKIE,
			$_FILES
		));

	$container->set('response', static fn () => new Response());

	// Create session Object
	$container->set('session', static fn () => (new SessionFactory())->newInstance($_COOKIE));

	// Create Html helper Object
	$container->set('html-helper', static fn () => (new HelperLocatorFactory())->newInstance());
	$container->set('component-helper', static fn () => (new HelperLocatorFactory())->newInstance());

	return $container;
};
