<?php declare(strict_types=1);
/**
 * Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     AnimeListClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2017  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient;

use Aura\Html\HelperLocatorFactory;
use Aura\Router\RouterContainer;
use Aura\Session\SessionFactory;
use Aviat\AnimeClient\API\Kitsu\{
	Auth as KitsuAuth,
	ListItem as KitsuListItem,
	KitsuModel
};
use Aviat\AnimeClient\API\MAL\{
	ListItem as MALListItem,
	Model as MALModel
};
use Aviat\AnimeClient\Model;
use Aviat\Banker\Pool;
use Aviat\Ion\Config;
use Aviat\Ion\Di\Container;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Zend\Diactoros\{Response, ServerRequestFactory};

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
	$request_logger = new Logger('request');
	$request_logger->pushHandler(new RotatingFileHandler(__DIR__ . '/logs/request.log', Logger::NOTICE));
	$container->setLogger($app_logger, 'default');
	$container->setLogger($request_logger, 'request');

	// -------------------------------------------------------------------------
	// Injected Objects
	// -------------------------------------------------------------------------

	// Create Config Object
	$container->set('config', function() use ($config_array) {
		return new Config($config_array);
	});

	// Create Cache Object
	$container->set('cache', function($container) {
		$logger = $container->getLogger();
		$config = $container->get('config')->get('cache');
		return new Pool($config, $logger);
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
	$container->set('kitsu-model', function($container) {
		$listItem = new KitsuListItem();
		$listItem->setContainer($container);
		$model = new KitsuModel($listItem);
		$model->setContainer($container);
		$cache = $container->get('cache');
		$model->setCache($cache);
		return $model;
	});
	$container->set('mal-model', function($container) {
		$listItem = new MALListItem();
		$listItem->setContainer($container);
		$model = new MALModel($listItem);
		$model->setContainer($container);
		return $model;
	});
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
		return new KitsuAuth($container);
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