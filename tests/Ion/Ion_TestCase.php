<?php declare(strict_types=1);
/**
 * Ion
 *
 * Building blocks for web development
 *
 * PHP version 7.2
 *
 * @package     Ion
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2019 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     3.0.0
 * @link        https://git.timshomepage.net/aviat/ion
 */

namespace Aviat\Ion\Tests;

use function Aviat\Ion\_dir;

use PHPUnit\Framework\TestCase;
use Zend\Diactoros\ServerRequestFactory;

define('ROOT_DIR', realpath(__DIR__ . '/../') . '/');
define('SRC_DIR', ROOT_DIR . 'src/');
define('TEST_DATA_DIR', __DIR__ . '/test_data');
define('TEST_VIEW_DIR', __DIR__ . '/test_views');

/**
 * Base class for TestCases
 */
class Ion_TestCase extends TestCase {
	// Test directory constants
	public const ROOT_DIR = ROOT_DIR;
	public const SRC_DIR = SRC_DIR;
	public const TEST_DATA_DIR = TEST_DATA_DIR;
	public const TEST_VIEW_DIR = TEST_VIEW_DIR;

	protected $container;
	protected static $staticContainer;
	protected static $session_handler;

	/*public static function setUpBeforeClass()
	{
		// Use mock session handler
		$session_handler = new TestSessionHandler();
		session_set_save_handler($session_handler, TRUE);
		self::$session_handler = $session_handler;
	}*/

	public function setUp(): void
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
					'database'   => '',
					'file' => ':memory:',
				],
				'cache' => [
					'type' => 'sqlite',
					'host' => '',
					'user' => '',
					'pass' => '',
					'port' => '',
					'name' => 'default',
					'database'   => '',
					'file' => ':memory:',
				]
			],
			'routes' => [
				'route_config' => [
					'asset_path' => '/assets'
				],
				'routes' => [

				]
			],
			'redis' => [
				'host' => (array_key_exists('REDIS_HOST', $_ENV)) ? $_ENV['REDIS_HOST'] : 'localhost',
				'database' => 13
			]
		];

		// Set up DI container
		$di = require('di.php');
		$container = $di($config_array);
		$container->set('session-handler', static function() {
			// Use mock session handler
			$session_handler = new TestSessionHandler();
			session_set_save_handler($session_handler, TRUE);
			return $session_handler;
		});

		$this->container = $container;
	}

	/**
	 * Set arbitrary superglobal values for testing purposes
	 *
	 * @param array $supers
	 * @return void
	 */
	public function setSuperGlobals(array $supers = []): void
	{
		$default = [
			'_SERVER' => $_SERVER,
			'_GET' => $_GET,
			'_POST' => $_POST,
			'_COOKIE' => $_COOKIE,
			'_FILES' => $_FILES
		];

		$request = call_user_func_array(
			[ServerRequestFactory::class, 'fromGlobals'],
			array_merge($default, $supers)
		);
		$this->container->setInstance('request', $request);
	}
}
// End of Ion_TestCase.php