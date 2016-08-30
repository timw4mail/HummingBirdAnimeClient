<?php
/**
 * Hummingbird Anime Client
 *
 * An API client for Hummingbird to manage anime and manga watch lists
 *
 * PHP version 5.6
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2016  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     3.1
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

/**
 * Bootstrap / Dependency Injection
 */
namespace Aviat\AnimeClient;

use Aura\Html\HelperLocatorFactory;
use Aura\Router\RouterContainer;
use Aura\Session\SessionFactory;
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
use Zend\Diactoros\ServerRequestFactory;
use Zend\Diactoros\Response;

use Aviat\Ion\Config;
use Aviat\Ion\Di\Container;
use Aviat\Ion\Cache\CacheManager;
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
	$container->set('config', function() {
		return new Config();
	});
	$container->setInstance('config', new Config($config_array));

	// Create Cache Object
	$container->set('cache', function($container) {
		return new CacheManager($container->get('config'));
	});

	// Create Aura Router Object
	$container->set('aura-router', function() {
		return new RouterContainer;
	});

	// Create Html helper Object
	$container->set('html-helper', function($container) {
		$html_helper = (new HelperLocatorFactory)->newInstance();
		$html_helper->set('menu', function() use ($container) {
			$menu_helper = new Helper\Menu();
			$menu_helper->setContainer($container);
			return $menu_helper;
		});

		return $html_helper;
	});

	// Create Request/Response Objects
	$container->set('request', function() {
		return ServerRequestFactory::fromGlobals(
			$_SERVER,
			$_GET,
			$_POST,
			$_COOKIE,
			$_FILES
		);
	});
	$container->set('response', function() {
		return new Response;
	});

	// Create session Object
	$container->set('session', function() {
		return (new SessionFactory())->newInstance($_COOKIE);
	});

	// Miscellaneous helper methods
	$container->set('util', function($container) {
		return new Util($container);
	});

	// Models
	$container->set('api-model', function($container) {
		return new Model\API($container);
	});
	$container->set('anime-model', function($container) {
		return new Model\Anime($container);
	});
	$container->set('manga-model', function($container) {
		return new Model\Manga($container);
	});
	$container->set('anime-collection-model', function($container) {
		return new Model\AnimeCollection($container);
	});

	// Miscellaneous Classes
	$container->set('auth', function($container) {
		return new HummingbirdAuth($container);
	});
	$container->set('url-generator', function($container) {
		return new UrlGenerator($container);
	});

	// -------------------------------------------------------------------------
	// Dispatcher
	// -------------------------------------------------------------------------
	$container->set('dispatcher', function($container) {
		return new Dispatcher($container);
	});

	return $container;
};

// End of bootstrap.php