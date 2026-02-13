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

use Aviat\AnimeClient\RenderHelper;
use Aviat\AnimeClient\UrlGenerator;
use Aviat\Ion\Di\Container;
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

	protected string $testDbFile = '';

	#[\Override]
	protected function setUp(): void
	{
		parent::setUp();

		$dbFile = __DIR__ . '/test_data/test.sqlite';
		$this->testDbFile = __DIR__ . '/test_data/test_' . uniqid() . '.sqlite';
		if (file_exists($dbFile))
		{
			copy($dbFile, $this->testDbFile);
		}

		$config_array = [
			'root' => self::ROOT_DIR,
			'asset_path' => '/assets',
			'img_cache_path' => _dir(self::TEST_DATA_DIR, 'images'),
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
				'file' => $this->testDbFile,
			],
			'view_path' => self::TEST_VIEW_DIR,
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

	#[\Override]
	protected function tearDown(): void
	{
		parent::tearDown();
		if ($this->testDbFile !== '' && file_exists($this->testDbFile))
		{
			unlink($this->testDbFile);
		}
	}

	/**
	 * Set arbitrary superglobal values for testing purposes
	 *
	 * @param array $supers
	 */
	public function setSuperGlobals($supers = []): void
	{
		$default = [
			'_SERVER' => array_merge($GLOBALS['_SERVER'], [
				'HTTP_HOST' => 'localhost',
				'SERVER_NAME' => 'localhost',
			]),
			'_GET' => $_GET,
			'_POST' => $_POST,
			'_COOKIE' => $_COOKIE,
			'_FILES' => $_FILES,
		];

		$combined = array_replace_recursive($default, $supers);

		$request = call_user_func_array(
			ServerRequestFactory::fromGlobals(...),
			array_values($combined),
		);

		if ($this->container instanceof Container)
		{
			$this->container->clearInstance('request');
			$this->container->setInstance('request', $request);

			$this->container->clearInstance('response');
			$this->container->set('response', static fn () => new HttpResponse());

			// Reset dependent objects
			$this->container->clearInstance('aura-router');
			$this->container->clearInstance('url-generator');
			$this->container->clearInstance('render-helper');
			$this->container->clearInstance('dispatcher');

			if (! $this instanceof DispatcherTest)
			{
				$this->container->get('dispatcher');
			}
		}
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
