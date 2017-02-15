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
 * @copyright   2015 - 2017  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Command;

use function Aviat\AnimeClient\loadToml;

use Aura\Session\SessionFactory;
use Aviat\AnimeClient\{
	AnimeClient,
	Model,
	Util
};
use Aviat\AnimeClient\API\CacheTrait;
use Aviat\AnimeClient\API\{Kitsu, MAL};
use Aviat\AnimeClient\API\Kitsu\KitsuRequestBuilder;
use Aviat\AnimeClient\API\MAL\MALRequestBuilder;
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
		$APPCONF_DIR = realpath("{$APP_DIR}/appConf/");
		$CONF_DIR = realpath("{$APP_DIR}/config/");
		require_once $APPCONF_DIR . '/base_config.php'; // $base_config

		$config = loadToml($CONF_DIR);
		$config_array = array_merge($base_config, $config);

		$di = function ($config_array) use ($APP_DIR) {
			$container = new Container();

			// -------------------------------------------------------------------------
			// Logging
			// -------------------------------------------------------------------------

			$app_logger = new Logger('animeclient');
			$app_logger->pushHandler(new NullHandler);
			$kitsu_request_logger = new Logger('kitsu-request');
			$kitsu_request_logger->pushHandler(new NullHandler);
			$mal_request_logger = new Logger('mal-request');
			$mal_request_logger->pushHandler(new NullHandler);
			$container->setLogger($app_logger, 'default');
			$container->setLogger($kitsu_request_logger, 'kitsu-request');
			$container->setLogger($mal_request_logger, 'mal-request');

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

			$container->set('util', function($container) {
				return new Util($container);
			});

			return $container;
		};

		return $di($config_array);
	}
}