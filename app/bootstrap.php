<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.1
 *
 * @copyright   2015 - 2023  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient;

use Aura\Html\HelperLocatorFactory;
use Aura\Router\RouterContainer;
use Aura\Session\SessionFactory;
use Aviat\AnimeClient\API\{Anilist, Kitsu};
use Aviat\AnimeClient\{Component, Model};
use Aviat\Banker\Teller;
use Aviat\Ion\Config;
use Aviat\Ion\Di\{Container, ContainerInterface};
use Laminas\Diactoros\ServerRequestFactory;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Psr\SimpleCache\CacheInterface;

use function Aviat\Ion\_dir;

if ( ! defined('HB_APP_DIR'))
{
	define('HB_APP_DIR', __DIR__);
	define('ROOT_DIR', dirname(HB_APP_DIR));
	define('TEMPLATE_DIR', _dir(HB_APP_DIR, 'templates'));
}

// -----------------------------------------------------------------------------
// Setup DI container
// -----------------------------------------------------------------------------
return static function (array $configArray = []): Container {
	$container = new Container();

	// -------------------------------------------------------------------------
	// Logging
	// -------------------------------------------------------------------------
	$LOG_DIR = _dir(HB_APP_DIR, 'logs');

	$appLogger = new Logger('animeclient');
	$appLogger->pushHandler(new RotatingFileHandler(_dir($LOG_DIR, 'app.log'), 2, Logger::WARNING));
	$container->setLogger($appLogger);

	foreach (['anilist-request', 'kitsu-request', 'kitsu-graphql'] as $channel)
	{
		$logger = new Logger($channel);
		$handler = new RotatingFileHandler(_dir($LOG_DIR, "{$channel}.log"), 2, Logger::WARNING);
		$handler->setFormatter(new JsonFormatter());
		$logger->pushHandler($handler);

		$container->setLogger($logger, $channel);
	}

	// -------------------------------------------------------------------------
	// Injected Objects
	// -------------------------------------------------------------------------

	// Create Config Object
	$container->set('config', static fn () => new Config($configArray));

	// Create Cache Object
	$container->set('cache', static function (ContainerInterface $container): CacheInterface {
		$logger = $container->getLogger();
		$config = $container->get('config')->get('cache');

		return new Teller($config, $logger);
	});

	// Create Aura Router Object
	$container->set('aura-router', static fn () => new RouterContainer());

	// Create Html helpers
	$container->set('html-helper', static function (ContainerInterface $container) {
		$htmlHelper = (new HelperLocatorFactory())->newInstance();
		$helpers = [
			'menu' => Helper\Menu::class,
			'field' => Helper\Form::class,
			'picture' => Helper\Picture::class,
		];

		foreach ($helpers as $name => $class)
		{
			$htmlHelper->set($name, static function () use ($class, $container) {
				$helper = new $class();
				$helper->setContainer($container);

				return $helper;
			});
		}

		return $htmlHelper;
	});

	// Create Component helpers
	$container->set('component-helper', static function (ContainerInterface $container) {
		$helper = (new HelperLocatorFactory())->newInstance();
		$components = [
			'animeCover' => Component\AnimeCover::class,
			'mangaCover' => Component\MangaCover::class,
			'character' => Component\Character::class,
			'media' => Component\Media::class,
			'tabs' => Component\Tabs::class,
			'verticalTabs' => Component\VerticalTabs::class,
		];

		foreach ($components as $name => $componentClass)
		{
			$helper->set($name, static function () use ($container, $componentClass) {
				$helper = new $componentClass();
				$helper->setContainer($container);

				return $helper;
			});
		}

		return $helper;
	});

	// Create Request Object
	$container->set('request', static fn () => ServerRequestFactory::fromGlobals(
		$GLOBALS['_SERVER'],
		$_GET,
		$_POST,
		$_COOKIE,
		$_FILES
	));

	// Create session Object
	$container->set('session', static fn () => (new SessionFactory())->newInstance($_COOKIE));

	// Models
	$container->set('kitsu-model', static function (ContainerInterface $container): Kitsu\Model {
		$requestBuilder = new Kitsu\RequestBuilder($container);
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
	$container->set('anilist-model', static function (ContainerInterface $container): Anilist\Model {
		$requestBuilder = new Anilist\RequestBuilder($container);
		$requestBuilder->setLogger($container->getLogger('anilist-request'));

		$listItem = new Anilist\ListItem();
		$listItem->setContainer($container);
		$listItem->setRequestBuilder($requestBuilder);

		$model = new Anilist\Model($listItem);
		$model->setContainer($container);
		$model->setRequestBuilder($requestBuilder);

		return $model;
	});
	$container->set('settings-model', static function ($container) {
		$model = new Model\Settings($container->get('config'));
		$model->setContainer($container);

		return $model;
	});

	$container->setSimple('anime-model', Model\Anime::class);
	$container->setSimple('manga-model', Model\Manga::class);
	$container->setSimple('anime-collection-model', Model\AnimeCollection::class);

	// Miscellaneous Classes
	$container->setSimple('util', Util::class);
	$container->setSimple('auth', Kitsu\Auth::class);
	$container->setSimple('url-generator', UrlGenerator::class);
	$container->setSimple('render-helper', RenderHelper::class);

	// -------------------------------------------------------------------------
	// Dispatcher
	// -------------------------------------------------------------------------
	$container->setSimple('dispatcher', Dispatcher::class);

	return $container;
};

// End of bootstrap.php
