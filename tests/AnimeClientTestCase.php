<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2018  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests;

use const Aviat\AnimeClient\SRC_DIR;

use function Aviat\Ion\_dir;

use Aura\Web\WebFactory;
use Aviat\Ion\Json;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;
use Zend\Diactoros\{
	Response as HttpResponse,
	ServerRequestFactory
};

\define('ROOT_DIR', __DIR__ . '/../');
\define('TEST_DATA_DIR', __DIR__ . '/test_data');
\define('TEST_VIEW_DIR', __DIR__ . '/test_views');

/**
 * Base class for TestCases
 */
class AnimeClientTestCase extends TestCase {
	use MatchesSnapshots;


	// Test directory constants
	const ROOT_DIR = ROOT_DIR;
	const SRC_DIR = SRC_DIR;
	const TEST_DATA_DIR = TEST_DATA_DIR;
	const TEST_VIEW_DIR = TEST_VIEW_DIR;

	protected $container;
	protected static $staticContainer;
	protected static $session_handler;

	public static function setUpBeforeClass()
	{
		// Use mock session handler
		//$session_handler = new TestSessionHandler();
		//session_set_save_handler($session_handler, TRUE);
		//self::$session_handler = $session_handler;

		// Remove test cache files
		$files = glob(_dir(TEST_DATA_DIR, 'cache', '*.json'));
		array_map('unlink', $files);
	}

	public function setUp()
	{
		parent::setUp();

		$ROOT_DIR = realpath(_dir(__DIR__, '/../'));
		$APP_DIR = _dir($ROOT_DIR, 'app');

		$config_array = [
			'asset_path' => '//localhost/assets/',
			'img_cache_path' => _dir(ROOT_DIR, 'public/images'),
			'data_cache_path' => _dir(TEST_DATA_DIR, 'cache'),
			'cache' => [
				'driver' => 'null',
				'connection' => []
			],
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
			'route_config' => [
				'asset_path' => '/assets'
			],
			'routes' => [

			],
			'mal' => [
				'username' => 'foo',
				'password' => 'bar'
			]
		];

		// Set up DI container
		$di = require _dir($APP_DIR, 'bootstrap.php');
		$container = $di($config_array);

		// Use mock session handler
		$container->set('session-handler', function() {
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
	public function setSuperGlobals($supers = [])
	{
		$default = [
			'_SERVER' => $_SERVER,
			'_GET' => $_GET,
			'_POST' => $_POST,
			'_COOKIE' => $_COOKIE,
			'_FILES' => $_FILES
		];

		$request = \call_user_func_array(
			[ServerRequestFactory::class, 'fromGlobals'],
			array_merge($default, $supers)
		);
		$this->container->setInstance('request', $request);
		$this->container->set('repsone', function() {
			return new HttpResponse();
		});
	}

	/**
	 * Simplify getting test data
	 *
	 * Takes multiple path arguments
	 *
	 * @return string - contents of the data file
	 */
	public function getMockFile()
	{
		$args = func_get_args();
		array_unshift($args, TEST_DATA_DIR);
		$filePath = implode(DIRECTORY_SEPARATOR, $args);

		return file_get_contents($filePath);
	}

	/**
	 * Simplify getting mocked test data
	 *
	 * Takes multiple path arguments
	 *
	 * @return mixed - the decoded data
	 */
	public function getMockFileData(...$args)
	{
		$rawData = $this->getMockFile(...$args);

		return Json::decode($rawData);
	}
}
// End of AnimeClientTestCase.php