<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient;

use Aura\Html\HelperLocatorFactory;
use Aura\Router\RouterContainer;
use Aura\Session\SessionFactory;
use Aviat\AnimeClient\API\{
	Anilist,
	Kitsu,
	Kitsu\KitsuJsonApiRequestBuilder
};
use Aviat\AnimeClient\Model;
use Aviat\Banker\Teller;
use Aviat\Ion\Config;
use Aviat\Ion\Di\Container;
use Aviat\Ion\Di\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Laminas\Diactoros\ServerRequestFactory;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

// -----------------------------------------------------------------------------
// Setup DI container
// -----------------------------------------------------------------------------
return static function (array $configArray = []): Container {
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
	$container->set('config', fn () => new Config($configArray));

	// Create Cache Object
	$container->set('cache', static function(ContainerInterface $container): CacheInterface {
		$logger = $container->getLogger();
		$config = $container->get('config')->get('cache');
		return new Teller($config, $logger);
	});

	// Create Aura Router Object
	$container->set('aura-router', fn() => new RouterContainer);

	// Create Html helper Object
	$container->set('html-helper', static function(ContainerInterface $container) {
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

	// Create Request Object
	$container->set('request', fn () => ServerRequestFactory::fromGlobals(
		$_SERVER,
		$_GET,
		$_POST,
		$_COOKIE,
		$_FILES
	));

	// Create session Object
	$container->set('session', fn () => (new SessionFactory())->newInstance($_COOKIE));

	// Miscellaneous helper methods
	$container->set('util', fn ($container) => new Util($container));

	// Models
	$container->set('kitsu-model', static function(ContainerInterface $container): Kitsu\Model {
		$jsonApiRequestBuilder = new KitsuJsonApiRequestBuilder($container);
		$jsonApiRequestBuilder->setLogger($container->getLogger('kitsu-request'));

		$requestBuilder = new Kitsu\KitsuRequestBuilder($container);
		$requestBuilder->setLogger($container->getLogger('kitsu-request'));

		$listItem = new Kitsu\ListItem();
		$listItem->setContainer($container);
		$listItem->setJsonApiRequestBuilder($jsonApiRequestBuilder)
			->setRequestBuilder($requestBuilder);

		$model = new Kitsu\Model($listItem);
		$model->setContainer($container);
		$model->setJsonApiRequestBuilder($jsonApiRequestBuilder)
			->setRequestBuilder($requestBuilder);

		$cache = $container->get('cache');
		$model->setCache($cache);
		return $model;
	});
	$container->set('anilist-model', static function(ContainerInterface $container): Anilist\Model {
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
	$container->set('anime-model', fn ($container) => new Model\Anime($container));
	$container->set('manga-model', fn ($container) => new Model\Manga($container));
	$container->set('anime-collection-model', fn ($container) => new Model\AnimeCollection($container));
	$container->set('manga-collection-model', fn ($container) => new Model\MangaCollection($container));
	$container->set('settings-model', static function($container) {
		$model = new Model\Settings($container->get('config'));
		$model->setContainer($container);
		return $model;
	});

	// Miscellaneous Classes
	$container->set('auth', fn ($container) => new Kitsu\Auth($container));
	$container->set('url-generator', fn ($container) => new UrlGenerator($container));

	// -------------------------------------------------------------------------
	// Dispatcher
	// -------------------------------------------------------------------------
	$container->set('dispatcher', fn ($container) => new Dispatcher($container));

	return $container;
};

// End of bootstrap.php