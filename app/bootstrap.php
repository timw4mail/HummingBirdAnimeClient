<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2018  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient;

use Aura\Html\HelperLocatorFactory;
use Aura\Router\RouterContainer;
use Aura\Session\SessionFactory;
use Aviat\AnimeClient\API\{
	Anilist,
	Kitsu,
	MAL,
	Kitsu\KitsuRequestBuilder,
	MAL\MALRequestBuilder
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
return function (array $configArray = []) {
	$container = new Container();

	// -------------------------------------------------------------------------
	// Logging
	// -------------------------------------------------------------------------

	$appLogger = new Logger('animeclient');
	$appLogger->pushHandler(new RotatingFileHandler(__DIR__ . '/logs/app.log', Logger::NOTICE));
	$anilistRequestLogger = new Logger('anilist-request');
	$anilistRequestLogger->pushHandler(new RotatingFileHandler(__DIR__ . '/logs/anilist_request.log', Logger::NOTICE));
	$kitsuRequestLogger = new Logger('kitsu-request');
	$kitsuRequestLogger->pushHandler(new RotatingFileHandler(__DIR__ . '/logs/kitsu_request.log', Logger::NOTICE));
	$malRequestLogger = new Logger('mal-request');
	$malRequestLogger->pushHandler(new RotatingFileHandler(__DIR__ . '/logs/mal_request.log', Logger::NOTICE));
	$container->setLogger($appLogger);
	$container->setLogger($anilistRequestLogger, 'anilist-request');
	$container->setLogger($kitsuRequestLogger, 'kitsu-request');
	$container->setLogger($malRequestLogger, 'mal-request');

	// -------------------------------------------------------------------------
	// Injected Objects
	// -------------------------------------------------------------------------

	// Create Config Object
	$container->set('config', function() use ($configArray) {
		return new Config($configArray);
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
		$htmlHelper = (new HelperLocatorFactory)->newInstance();
		$htmlHelper->set('menu', function() use ($container) {
			$menuHelper = new Helper\Menu();
			$menuHelper->setContainer($container);
			return $menuHelper;
		});

		return $htmlHelper;
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
		$requestBuilder = new KitsuRequestBuilder();
		$requestBuilder->setLogger($container->getLogger('kitsu-request'));

		$listItem = new Kitsu\ListItem();
		$listItem->setContainer($container);
		$listItem->setRequestBuilder($requestBuilder);

		$model = new Kitsu\Model($listItem);
		$model->setContainer($container);
		$model->setRequestBuilder($requestBuilder);

		$cache = $container->get('cache');
		$model->setCache($cache);
		return $model;
	});
	$container->set('mal-model', function($container) {
		$requestBuilder = new MALRequestBuilder();
		$requestBuilder->setLogger($container->getLogger('mal-request'));

		$listItem = new MAL\ListItem();
		$listItem->setContainer($container);
		$listItem->setRequestBuilder($requestBuilder);

		$model = new MAL\Model($listItem);
		$model->setContainer($container);
		$model->setRequestBuilder($requestBuilder);
		return $model;
	});
	$container->set('anilist-model', function($container) {
		$requestBuilder = new Anilist\AnilistRequestBuilder();
		$requestBuilder->setLogger($container->getLogger('anilist-request'));

		$listItem = new Anilist\ListItem();
		$listItem->setContainer($container);
		$listItem->setRequestBuilder($requestBuilder);

		$model = new Anilist\Model($listItem);
		$model->setContainer($container);
		$model->setRequestBuilder($requestBuilder);

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
	$container->set('manga-collection-model', function($container) {
		return new Model\MangaCollection($container);
	});

	// Miscellaneous Classes
	$container->set('auth', function($container) {
		return new Kitsu\Auth($container);
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