<?php declare(strict_types=1);

use Aura\Html\HelperLocatorFactory;
use Aura\Session\SessionFactory;
use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\Response;

use Aviat\Ion\Config;
use Aviat\Ion\Di\Container;

// -----------------------------------------------------------------------------
// Setup DI container
// -----------------------------------------------------------------------------
return static function(array $config_array = []) {
	$container = new Container();

	$container->set('config', static function() {
		return new Config([]);
	});

	$container->setInstance('config', new Config($config_array));

	$container->set('request', static function() {
		return ServerRequestFactory::fromGlobals(
			$GLOBALS['_SERVER'],
			$_GET,
			$_POST,
			$_COOKIE,
			$_FILES
		);
	});

	$container->set('response', static function() {
		return new Response();
	});

	// Create session Object
	$container->set('session', static function() {
		return (new SessionFactory())->newInstance($_COOKIE);
	});

	// Create Html helper Object
	$container->set('html-helper', fn() => (new HelperLocatorFactory)->newInstance());
	$container->set('component-helper', fn() => (new HelperLocatorFactory)->newInstance());

	return $container;
};