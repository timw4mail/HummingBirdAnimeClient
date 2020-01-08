<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.2
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient;

use Aura\Html\HelperLocatorFactory;
use Aura\Router\RouterContainer;
use Aura\Session\SessionFactory;
use Aviat\AnimeClient\API\{
	Anilist,
	Kitsu,
	Kitsu\KitsuRequestBuilder
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
return static function ($configArray = []) {
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

	$container->setLogger($appLogger);
	$container->setLogger($anilistRequestLogger, 'anilist-request');
	$container->setLogger($kitsuRequestLogger, 'kitsu-request');

	// -------------------------------------------------------------------------
	// Injected Objects
	// -------------------------------------------------------------------------

	// Create Config Object
	$container->set('config', static function() use ($configArray) {
		return new Config($configArray);
	});

	// Create Cache Object
	$container->set('cache', static function($container): Pool {
		$logger = $container->getLogger();
		$config = $container->get('config')->get('cache');
		return new Pool($config, $logger);
	});

	// Create List Cache

	// Create Aura Router Object
	$container->set('aura-router', static function() {
		return new RouterContainer;
	});

	// Create Html helper Object
	$container->set('html-helper', static function($container) {
		$htmlHelper = (new HelperLocatorFactory)->newInstance();
		$htmlHelper->set('menu', static function() use ($container) {
			$menuHelper = new Helper\Menu();
			$menuHelper->setContainer($container);
			return $menuHelper;
		});
		$htmlHelper->set('field', static function() use ($container) {
			$formHelper = new Helper\Form();
			$formHelper->setContainer($container);
			return $formHelper;
		});
		$htmlHelper->set('picture', static function() use ($container) {
			$pictureHelper = new Helper\Picture();
			$pictureHelper->setContainer($container);
			return $pictureHelper;
		});

		return $htmlHelper;
	});

	// Create Request/Response Objects
	$container->set('request', static function() {
		return ServerRequestFactory::fromGlobals(
			$_SERVER,
			$_GET,
			$_POST,
			$_COOKIE,
			$_FILES
		);
	});
	$container->set('response', static function() {
		return new Response;
	});

	// Create session Object
	$container->set('session', static function() {
		return (new SessionFactory())->newInstance($_COOKIE);
	});

	// Miscellaneous helper methods
	$container->set('util', static function($container): Util {
		return new Util($container);
	});

	// Models
	$container->set('kitsu-model', static function($container): Kitsu\Model {
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
	$container->set('anilist-model', static function($container): Anilist\Model {
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

	$container->set('anime-model', static function($container) {
		return new Model\Anime($container);
	});
	$container->set('manga-model', static function($container) {
		return new Model\Manga($container);
	});
	$container->set('anime-collection-model', static function($container) {
		return new Model\AnimeCollection($container);
	});
	$container->set('manga-collection-model', static function($container) {
		return new Model\MangaCollection($container);
	});
	$container->set('settings-model', static function($container) {
		$model =  new Model\Settings($container->get('config'));
		$model->setContainer($container);
		return $model;
	});

	// Miscellaneous Classes
	$container->set('auth', static function($container) {
		return new Kitsu\Auth($container);
	});
	$container->set('url-generator', static function($container) {
		return new UrlGenerator($container);
	});

	// -------------------------------------------------------------------------
	// Dispatcher
	// -------------------------------------------------------------------------
	$container->set('dispatcher', static function($container) {
		return new Dispatcher($container);
	});

	return $container;
};

// End of bootstrap.php