<?php
/**
 * Hummingbird Anime Client
 *
 * An API client for Hummingbird to manage anime and manga watch lists
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren
 * @copyright   Copyright (c) 2015 - 2016
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 * @license     MIT
 */

namespace Aviat\AnimeClient\Command;

use Aura\Session\SessionFactory;
use ConsoleKit\Command;
use ConsoleKit\Widgets\Box;

use Aviat\Ion\Config;
use Aviat\Ion\Di\Container;
use Aviat\Ion\Cache\CacheManager;

use Aviat\AnimeClient\AnimeClient;
use Aviat\AnimeClient\Auth\HummingbirdAuth;
use Aviat\AnimeClient\Model;
use Aviat\AnimeClient\Util;

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
		$CONF_DIR = realpath(__DIR__ . '/../../../../app/config/');
		require_once $CONF_DIR . '/base_config.php'; // $base_config

		$config = AnimeClient::load_toml($CONF_DIR);
		$config_array = array_merge($base_config, $config);

		$di = function ($config_array) {
			$container = new Container();

			// Create Config Object
			$config = new Config($config_array);
			$container->set('config', $config);

			// Create Cache Object
			$container->set('cache', new CacheManager($config));

			// Create session Object
			$session = (new SessionFactory())->newInstance($_COOKIE);
			$container->set('session', $session);

			// Models
			$container->set('api-model', new Model\API($container));
			$container->set('anime-model', new Model\Anime($container));
			$container->set('manga-model', new Model\Manga($container));

			$container->set('auth', new HummingbirdAuth($container));
			$container->set('util', new Util($container));

			return $container;
		};

		return $di($config_array);
	}
}