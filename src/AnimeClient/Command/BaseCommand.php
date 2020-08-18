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
 * @version     5.1
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Command;

use Monolog\Formatter\JsonFormatter;
use function Aviat\AnimeClient\loadToml;
use function Aviat\AnimeClient\loadTomlFile;

use Aura\Router\RouterContainer;
use Aura\Session\SessionFactory;
use Aviat\AnimeClient\{Model, UrlGenerator, Util};
use Aviat\AnimeClient\API\{Anilist, CacheTrait, Kitsu};
use Aviat\Banker\Teller;
use Aviat\Ion\Config;
use Aviat\Ion\Di\{Container, ContainerInterface, ContainerAware};
use ConsoleKit\{Colors, Command, ConsoleException};
use ConsoleKit\Widgets\Box;
use Laminas\Diactoros\{Response, ServerRequestFactory};
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;

/**
 * Base class for console command setup
 */
abstract class BaseCommand extends Command {
	use CacheTrait;
	use ContainerAware;

	/**
	 * Echo text in a box
	 *
	 * @param string|array $message
	 * @param string|int|null $fgColor
	 * @param string|int|null $bgColor
	 * @return void
	 */
	public function echoBox($message, $fgColor = NULL, $bgColor = NULL): void
	{
		if (is_array($message))
		{
			$message = implode("\n", $message);
		}

		// color message
		$message = Colors::colorize($message, $fgColor, $bgColor);

		// create the box
		$box = new Box($this->getConsole(), $message);

		$box->write();

		echo "\n";
	}

	public function echo(string $message): void
	{
		$this->_line($message);
	}

	public function echoSuccess(string $message): void
	{
		$this->_line($message, Colors::GREEN | Colors::BOLD, Colors::BLACK);
	}

	public function echoWarning(string $message): void
	{
		$this->_line($message, Colors::YELLOW | Colors::BOLD, Colors::BLACK);
	}

	public function echoWarningBox(string $message): void
	{
		$this->echoBox($message, Colors::YELLOW | Colors::BOLD, Colors::BLACK);
	}

	public function echoError(string $message): void
	{
		$this->_line($message, Colors::RED | Colors::BOLD, Colors::BLACK);
	}

	public function echoErrorBox(string $message): void
	{
		$this->echoBox($message, Colors::RED | Colors::BOLD, Colors::BLACK);
	}

	public function clearLine(): void
	{
		$this->getConsole()->write("\r\e[2K");
	}

	/**
	 * Setup the Di container
	 *
	 * @return Containerinterface
	 */
	public function setupContainer(): ContainerInterface
	{
		$APP_DIR = realpath(__DIR__ . '/../../../app');
		$APPCONF_DIR = realpath("{$APP_DIR}/appConf/");
		$CONF_DIR = realpath("{$APP_DIR}/config/");
		$baseConfig = require $APPCONF_DIR . '/base_config.php';

		$config = loadToml($CONF_DIR);

		$overrideFile = $CONF_DIR . '/admin-override.toml';
		$overrideConfig = file_exists($overrideFile)
			? loadTomlFile($overrideFile)
			: [];

		$configArray = array_replace_recursive($baseConfig, $config, $overrideConfig);

		return $this->_di($configArray, $APP_DIR);
	}

	private function _line(string $message, $fgColor = NULL, $bgColor = NULL): void
	{
		$message = Colors::colorize($message, $fgColor, $bgColor);
		$this->getConsole()->writeln($message);
	}

	private function _di(array $configArray, string $APP_DIR): ContainerInterface
	{
		$container = new Container();

		// -------------------------------------------------------------------------
		// Logging
		// -------------------------------------------------------------------------

		$appLogger = new Logger('animeclient');
		$appLogger->pushHandler(new RotatingFileHandler($APP_DIR . '/logs/app-cli.log', 2, Logger::WARNING));
		$container->setLogger($appLogger);

		foreach (['anilist-request-cli', 'kitsu-request-cli'] as $channel)
		{
			$logger = new Logger($channel);
			$handler = new RotatingFileHandler( "{$APP_DIR}/logs/{$channel}.log", 2, Logger::WARNING);
			$handler->setFormatter(new JsonFormatter());
			$logger->pushHandler($handler);

			$container->setLogger($logger, $channel);
		}

		// Create Config Object
		$container->set('config', fn () => new Config($configArray));

		// Create Cache Object
		$container->set('cache', static function($container) {
			$logger = $container->getLogger();
			$config = $container->get('config')->get('cache');
			return new Teller($config, $logger);
		});

		// Create Aura Router Object
		$container->set('aura-router', fn () => new RouterContainer);

		// Create Request/Response Objects
		$container->set('request', fn () => ServerRequestFactory::fromGlobals(
			$_SERVER,
			$_GET,
			$_POST,
			$_COOKIE,
			$_FILES
		));
		$container->set('response', fn () => new Response);

		// Create session Object
		$container->set('session', fn () => (new SessionFactory())->newInstance($_COOKIE));

		// Models
		$container->set('kitsu-model', static function($container): Kitsu\Model {
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
		$container->set('anilist-model', static function ($container): Anilist\Model {
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
		$container->set('settings-model', static function($container): Model\Settings {
			$model =  new Model\Settings($container->get('config'));
			$model->setContainer($container);
			return $model;
		});

		$container->set('auth', fn ($container) => new Kitsu\Auth($container));

		$container->set('url-generator', fn ($container) => new UrlGenerator($container));

		$container->set('util', fn ($container) => new Util($container));

		return $container;
	}
}