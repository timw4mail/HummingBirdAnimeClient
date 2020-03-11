<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.3
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Command;

use function Aviat\AnimeClient\loadToml;
use function Aviat\AnimeClient\loadTomlFile;

use Aura\Router\RouterContainer;
use Aura\Session\SessionFactory;
use Aviat\AnimeClient\{Model, UrlGenerator, Util};
use Aviat\AnimeClient\API\{Anilist, CacheTrait, Kitsu};
use Aviat\AnimeClient\API\Kitsu\KitsuRequestBuilder;
use Aviat\Banker\Pool;
use Aviat\Ion\Config;
use Aviat\Ion\Di\{Container, ContainerAware};
use ConsoleKit\{Command, ConsoleException};
use ConsoleKit\Widgets\Box;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Zend\Diactoros\{Response, ServerRequestFactory};

/**
 * Base class for console command setup
 */
abstract class BaseCommand extends Command {
	use CacheTrait;
	use ContainerAware;

	/**
	 * Echo text in a box
	 *
	 * @param string $message
	 * @return void
	 */
	protected function echoBox($message): void
	{
		try
		{
			echo "\n";
			$box = new Box($this->getConsole(), $message);
			$box->write();
			echo "\n";
		}
		catch (ConsoleException $e)
		{
			// oops
		}
	}

	/**
	 * Setup the Di container
	 *
	 * @return Container
	 */
	protected function setupContainer(): Container
	{
		$APP_DIR = realpath(__DIR__ . '/../../app');
		$APPCONF_DIR = realpath("{$APP_DIR}/appConf/");
		$CONF_DIR = realpath("{$APP_DIR}/config/");
		$baseConfig = require $APPCONF_DIR . '/base_config.php';

		$config = loadToml($CONF_DIR);

		$overrideFile = $CONF_DIR . '/admin-override.toml';
		$overrideConfig = file_exists($overrideFile)
			? loadTomlFile($overrideFile)
			: [];

		$configArray = array_replace_recursive($baseConfig, $config, $overrideConfig);

		$di = static function ($configArray) use ($APP_DIR): Container {
			$container = new Container();

			// -------------------------------------------------------------------------
			// Logging
			// -------------------------------------------------------------------------

			$app_logger = new Logger('animeclient');
			$app_logger->pushHandler(new RotatingFileHandler($APP_DIR . '/logs/app-cli.log', Logger::NOTICE));

			$kitsu_request_logger = new Logger('kitsu-request');
			$kitsu_request_logger->pushHandler(new RotatingFileHandler($APP_DIR . '/logs/kitsu_request-cli.log', Logger::NOTICE));

			$anilistRequestLogger = new Logger('anilist-request');
			$anilistRequestLogger->pushHandler(new RotatingFileHandler($APP_DIR . '/logs/anilist_request-cli.log', Logger::NOTICE));

			$container->setLogger($app_logger);
			$container->setLogger($anilistRequestLogger, 'anilist-request');
			$container->setLogger($kitsu_request_logger, 'kitsu-request');

			// Create Config Object
			$container->set('config', static function() use ($configArray): Config {
				return new Config($configArray);
			});

			// Create Cache Object
			$container->set('cache', static function($container) {
				$logger = $container->getLogger();
				$config = $container->get('config')->get('cache');
				return new Pool($config, $logger);
			});

			// Create Aura Router Object
			$container->set('aura-router', static function() {
				return new RouterContainer;
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
			$container->set('response', static function(): Response {
				return new Response;
			});

			// Create session Object
			$container->set('session', static function() {
				return (new SessionFactory())->newInstance($_COOKIE);
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
			$container->set('anilist-model', static function ($container): Anilist\Model {
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
			$container->set('settings-model', static function($container): Model\Settings {
				$model =  new Model\Settings($container->get('config'));
				$model->setContainer($container);
				return $model;
			});

			$container->set('auth', static function($container): Kitsu\Auth {
				return new Kitsu\Auth($container);
			});

			$container->set('url-generator', static function($container): UrlGenerator {
				return new UrlGenerator($container);
			});

			$container->set('util', static function($container): Util {
				return new Util($container);
			});

			return $container;
		};

		return $di($configArray);
	}
}