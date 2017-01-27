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

namespace Aviat\AnimeClient\Command;

use Aura\Session\SessionFactory;
use Aviat\AnimeClient\{
	AnimeClient,
	Model,
	Util
};
use Aviat\AnimeClient\API\CacheTrait;
use Aviat\AnimeClient\API\Kitsu\{
	Auth as KitsuAuth,
	ListItem as KitsuListItem,
	Model as KitsuModel
};
use Aviat\AnimeClient\API\MAL\{
	ListItem as MALListItem,
	Model as MALModel
};
use Aviat\Banker\Pool;
use Aviat\Ion\Config;
use Aviat\Ion\Di\{Container, ContainerAware};
use ConsoleKit\Command;
use ConsoleKit\Widgets\Box;
use Monolog\Handler\NullHandler;
use Monolog\Logger;

/**
 * Base class for console command setup
 */
class BaseCommand extends Command {
	use CacheTrait;
	use ContainerAware;

	/**
	 * Echo text in a box
	 *
	 * @param string $message
	 * @return void
	 */
	protected function echoBox($message)
	{
		echo "\n";
		$box = new Box($this->getConsole(), $message);
		$box->write();
		echo "\n";
	}

	/**
	 * Setup the Di container
	 *
	 * @return Container
	 */
	protected function setupContainer()
	{
		$APP_DIR = realpath(__DIR__ . '/../../app');
		$CONF_DIR = realpath("{$APP_DIR}/config/");
		require_once $CONF_DIR . '/base_config.php'; // $base_config

		$config = AnimeClient::loadToml($CONF_DIR);
		$config_array = array_merge($base_config, $config);

		$di = function ($config_array) use ($APP_DIR) {
			$container = new Container();
			
			// -------------------------------------------------------------------------
			// Logging
			// -------------------------------------------------------------------------

			$app_logger = new Logger('animeclient');
			$app_logger->pushHandler(new NullHandler);
			$request_logger = new Logger('request');
			$request_logger->pushHandler(new NullHandler);
			$container->setLogger($app_logger, 'default');
			$container->setLogger($request_logger, 'request');
			
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

			// Create session Object
			$container->set('session', function() {
				return (new SessionFactory())->newInstance($_COOKIE);
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
			$container->set('util', function($container) {
				return new Util($container);
			});

			return $container;
		};

		return $di($config_array);
	}
}