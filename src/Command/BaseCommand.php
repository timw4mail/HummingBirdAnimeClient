<?php declare(strict_types=1);
/**
 * Hummingbird Anime Client
 *
 * An API client for Hummingbird to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2016  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     3.1
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Command;

use Aura\Session\SessionFactory;
use Aviat\AnimeClient\AnimeClient;
use Aviat\AnimeClient\Auth\HummingbirdAuth;
use Aviat\AnimeClient\Model;
use Aviat\AnimeClient\Util;
use Aviat\Ion\Cache\CacheManager;
use Aviat\Ion\Config;
use Aviat\Ion\Di\Container;
use ConsoleKit\Command;
use ConsoleKit\Widgets\Box;

/**
 * Base class for console command setup
 */
class BaseCommand extends Command {
	use \Aviat\Ion\Di\ContainerAware;

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
		$CONF_DIR = realpath(__DIR__ . '/../../app/config/');
		require_once $CONF_DIR . '/base_config.php'; // $base_config

		$config = AnimeClient::load_toml($CONF_DIR);
		$config_array = array_merge($base_config, $config);

		$di = function ($config_array) {
			$container = new Container();

			// Create Config Object
			$container->set('config', function() {
				return new Config();
			});
			$container->setInstance('config', $config_array);

			// Create Cache Object
			$container->set('cache', function($container) {
				return new CacheManager($container->get('config'));
			});

			// Create session Object
			$container->set('session', function() {
				return (new SessionFactory())->newInstance($_COOKIE);
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

			$container->set('auth', function($container) {
				return new HummingbirdAuth($container);
			});
			$container->set('util', function($container) {
				return new Util($container);
			});

			return $container;
		};

		return $di($config_array);
	}
}