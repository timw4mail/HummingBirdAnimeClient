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

namespace Aviat\AnimeClient\Command;

use Aura\Router\RouterContainer;

use Aura\Session\SessionFactory;
use Aviat\AnimeClient\API\{Anilist, CacheTrait, Kitsu};

use Aviat\AnimeClient\{Model, UrlGenerator, Util};
use Aviat\Banker\Teller;
use Aviat\Ion\Config;
use Aviat\Ion\Di\{Container, ContainerAware, ContainerInterface};
use ConsoleKit\Widgets\Box;
use ConsoleKit\{Colors, Command, ConsoleException};
use Laminas\Diactoros\{Response, ServerRequestFactory};
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use function Aviat\AnimeClient\{loadConfig, loadTomlFile};
use function Aviat\Ion\_dir;
use const Aviat\AnimeClient\SRC_DIR;

/**
 * Base class for console command setup
 */
abstract class BaseCommand extends Command
{
	use CacheTrait;
	use ContainerAware;

	/**
	 * Echo text in a box
	 */
	public function echoBox(string|array $message, string|int|null $fgColor = NULL, string|int|null $bgColor = NULL): void
	{
		if (is_array($message))
		{
			$message = implode("\n", $message);
		}

		if ($fgColor !== NULL)
		{
			$fgColor = (int) $fgColor;
		}

		if ($bgColor !== NULL)
		{
			$bgColor = (int) $bgColor;
		}

		// Colorize the CLI output
		// the documented type for the function is wrong
		// @phpstan-ignore-next-line
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
	 */
	public function setupContainer(): ContainerInterface
	{
		$APP_DIR = _dir(dirname(SRC_DIR, 2), 'app');
		$APPCONF_DIR = _dir($APP_DIR, 'appConf');
		$CONF_DIR = _dir($APP_DIR, 'config');
		$baseConfig = require _dir($APPCONF_DIR, 'base_config.php');

		$config = loadConfig($CONF_DIR);

		$overrideFile = _dir($CONF_DIR, 'admin-override.toml');
		$overrideConfig = file_exists($overrideFile)
			? loadTomlFile($overrideFile)
			: [];

		$configArray = array_replace_recursive($baseConfig, $config, $overrideConfig);

		return $this->_di($configArray, $APP_DIR);
	}

	private function _line(string $message, int|string|null $fgColor = NULL, int|string|null $bgColor = NULL): void
	{
		if ($fgColor !== NULL)
		{
			$fgColor = (int) $fgColor;
		}

		if ($bgColor !== NULL)
		{
			$bgColor = (int) $bgColor;
		}

		// Colorize the CLI output
		// the documented type for the function is wrong
		// @phpstan-ignore-next-line
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

		foreach (['kitsu-request', 'anilist-request', 'anilist-request-cli', 'kitsu-request-cli'] as $channel)
		{
			$logger = new Logger($channel);
			$handler = new RotatingFileHandler("{$APP_DIR}/logs/{$channel}.log", 2, Logger::WARNING);
			$handler->setFormatter(new JsonFormatter());
			$logger->pushHandler($handler);

			$container->setLogger($logger, $channel);
		}

		// Create Config Object
		$container->set('config', static fn () => new Config($configArray));

		// Create Cache Object
		$container->set('cache', static function ($container): Teller {
			$logger = $container->getLogger();
			$config = $container->get('config')->get('cache');

			return new Teller($config, $logger);
		});

		// Create Aura Router Object
		$container->set('aura-router', static fn () => new RouterContainer());

		// Create Request/Response Objects
		$container->set('request', static fn () => ServerRequestFactory::fromGlobals(
			$GLOBALS['_SERVER'],
			$_GET,
			$_POST,
			$_COOKIE,
			$_FILES
		));
		$container->set('response', static fn () => new Response());

		// Create session Object
		$container->set('session', static fn () => (new SessionFactory())->newInstance($_COOKIE));

		// Models
		$container->set('kitsu-model', static function ($container): Kitsu\Model {
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
		$container->set('settings-model', static function ($container): Model\Settings {
			$model = new Model\Settings($container->get('config'));
			$model->setContainer($container);

			return $model;
		});

		$container->set('auth', static fn ($container) => new Kitsu\Auth($container));

		$container->set('url-generator', static fn ($container) => new UrlGenerator($container));

		$container->set('util', static fn ($container) => new Util($container));

		return $container;
	}
}
