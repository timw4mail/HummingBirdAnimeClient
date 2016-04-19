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

use Aviat\Ion\Di\Container;
use Aviat\Ion\Cache\CacheManager;
use Aviat\AnimeClient\Config;
use Aviat\AnimeClient\AnimeClient;
use Aviat\AnimeClient\Auth\HummingbirdAuth;
use Aviat\AnimeClient\Model;

/**
 * Base class for console command setup
 */
class BaseCommand extends \ConsoleKit\Command {
	use \Aviat\Ion\Di\ContainerAware;

	protected function setupContainer()
	{
		$CONF_DIR = __DIR__ . '/../../../../app/config/';
		require_once $CONF_DIR . '/base_config.php'; // $base_config

		$config = AnimeClient::load_toml($CONF_DIR);
		$config_array = array_merge($base_config, $config);

		$di = function ($config_array) {
			$container = new Container();

			// Create Config Object
			$config = new Config($config_array);
			$container->set('config', $config);

			// Create Cache Object
			$container->set('cache', new CacheManager($container));

			// Create session Object
			$session = (new SessionFactory())->newInstance($_COOKIE);
			$container->set('session', $session);

			// Models
			$container->set('api-model', new Model\API($container));
			$container->set('anime-model', new Model\Anime($container));
			$container->set('manga-model', new Model\Manga($container));

			$container->set('auth', new HummingbirdAuth($container));

			return $container;
		};

		return $di($config_array);
	}
}