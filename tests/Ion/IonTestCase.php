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

namespace Aviat\Ion\Tests;

use Aviat\Ion\Di\ContainerInterface;

use Laminas\Diactoros\ServerRequestFactory;
use PHPUnit\Framework\TestCase;
use function Aviat\Ion\_dir;

/**
 * Base class for TestCases
 */
class IonTestCase extends TestCase
{
	// Test directory constants
	final public const ROOT_DIR = AC_TEST_ROOT_DIR;
	final public const SRC_DIR = SRC_DIR;
	final public const TEST_DATA_DIR = __DIR__ . '/test_data';
	final public const TEST_VIEW_DIR = __DIR__ . '/test_views';

	protected ContainerInterface $container;
	protected static $staticContainer;
	protected static $session_handler;

	/*public static function setUpBeforeClass()
	{
		// Use mock session handler
		$session_handler = new TestSessionHandler();
		session_set_save_handler($session_handler, TRUE);
		self::$session_handler = $session_handler;
	}*/

	protected function setUp(): void
	{
		parent::setUp();

		$ROOT_DIR = realpath(_dir(__DIR__, '/../'));
		$APP_DIR = _dir($ROOT_DIR, 'app');

		$config_array = [
			'asset_path' => '//localhost/assets/',
			'img_cache_path' => _dir(ROOT_DIR, 'public/images'),
			'database' => [
				'collection' => [
					'type' => 'sqlite',
					'host' => '',
					'user' => '',
					'pass' => '',
					'port' => '',
					'name' => 'default',
					'database' => '',
					'file' => ':memory:',
				],
				'cache' => [
					'type' => 'sqlite',
					'host' => '',
					'user' => '',
					'pass' => '',
					'port' => '',
					'name' => 'default',
					'database' => '',
					'file' => ':memory:',
				],
			],
			'routes' => [
				'route_config' => [
					'asset_path' => '/assets',
				],
				'routes' => [

				],
			],
			'redis' => [
				'host' => (array_key_exists('REDIS_HOST', $_ENV)) ? $_ENV['REDIS_HOST'] : 'localhost',
				'database' => 13,
			],
		];

		// Set up DI container
		$di = require 'di.php';
		$container = $di($config_array);
		$container->set('session-handler', static function (): TestSessionHandler {
			// Use mock session handler
			$session_handler = new TestSessionHandler();
			session_set_save_handler($session_handler, TRUE);

			return $session_handler;
		});

		$this->container = $container;
	}

	/**
	 * Set arbitrary superglobal values for testing purposes
	 */
	public function setSuperGlobals(array $supers = []): void
	{
		$default = [
			'_SERVER' => $GLOBALS['_SERVER'],
			'_GET' => $_GET,
			'_POST' => $_POST,
			'_COOKIE' => $_COOKIE,
			'_FILES' => $_FILES,
		];

		$request = call_user_func_array(
			ServerRequestFactory::fromGlobals(...),
			array_merge($default, $supers)
		);
		$this->container->setInstance('request', $request);
	}
}
// End of IonTestCase.php
