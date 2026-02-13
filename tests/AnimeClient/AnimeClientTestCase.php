<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.4
 *
 * @copyright   2015 - 2026  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.3
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests;

use Aviat\Ion\Di\ContainerAware;
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Json;
use Laminas\Diactoros\Response as HttpResponse;
use Laminas\Diactoros\ServerRequestFactory;
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;

use function Aviat\Ion\_dir;
use function call_user_func_array;

/**
 * Base class for TestCases
 */
class AnimeClientTestCase extends TestCase
{
	use ContainerAware;

	use MatchesSnapshots;

	// Test directory constants
	final public const ROOT_DIR = AC_TEST_ROOT_DIR;
	final public const SRC_DIR = SRC_DIR;
	final public const TEST_DATA_DIR = __DIR__ . '/test_data';
	final public const TEST_VIEW_DIR = __DIR__ . '/test_views';

	protected ContainerInterface $container;

	#[\Override]
	public static function setUpBeforeClass(): void
	{
		// Remove test cache files
		$files = glob(_dir(self::TEST_DATA_DIR, 'cache', '*.json'));
		array_map(unlink(...), $files);
	}

	#[\Override]
	protected function setUp(): void
	{
		parent::setUp();

		$dbFile = __DIR__ . '/test_data/test.sqlite';
		$activeDbFile = __DIR__ . '/test_data/active_test.sqlite';
		if (file_exists($dbFile))
		{
			copy($dbFile, $activeDbFile);
		}

		$config_array = [
			'root' => self::ROOT_DIR,
			'asset_path' => '/assets',
			'img_cache_path' => _dir(self::ROOT_DIR, 'public/images'),
			'data_cache_path' => _dir(self::TEST_DATA_DIR, 'cache'),
			'cache' => [
				'driver' => 'null',
				'connection' => [],
			],
			'database' => [
				'type' => 'sqlite',
				'host' => '',
				'user' => '',
				'pass' => '',
				'port' => '',
				'name' => 'default',
				'database' => '',
				'file' => __DIR__ . '/test_data/active_test.sqlite',
			],
			'routes' => require __DIR__ . '/../../app/appConf/routes.php',
		];

		// Set up DI container
		$di = require self::ROOT_DIR . '/app/bootstrap.php';
		$container = $di($config_array);

		// Initialize routes
		if (! $this instanceof DispatcherTest)
		{
			$container->get('dispatcher');
		}

		// Use mock session handler
		$container->set('session-handler', static function (): TestSessionHandler {
			$session_handler = new TestSessionHandler();
			session_set_save_handler($session_handler, true);

			return $session_handler;
		});

		$this->container = $container;
	}

	/**
	 * Set arbitrary superglobal values for testing purposes
	 *
	 * @param array $supers
	 */
	public function setSuperGlobals($supers = []): void
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
			array_values(array_merge($default, $supers)),
		);
		$this->container->setInstance('request', $request);
		$this->container->set('response', static fn () => new HttpResponse());
	}

	/**
	 * Simplify getting test data
	 *
	 * Takes multiple path arguments
	 *
	 * @return string - contents of the data file
	 */
	public function getMockFile(): string
	{
		$args = func_get_args();
		array_unshift($args, self::TEST_DATA_DIR);
		$filePath = implode(DIRECTORY_SEPARATOR, $args);

		return file_get_contents($filePath);
	}

	/**
	 * Simplify getting mocked test data
	 *
	 * Takes multiple path arguments
	 *
	 * @param array $args
	 * @return mixed - the decoded data
	 */
	public function getMockFileData(mixed ...$args): mixed
	{
		$rawData = $this->getMockFile(...$args);

		return Json::decode($rawData);
	}
}

// End of AnimeClientTestCase.php
