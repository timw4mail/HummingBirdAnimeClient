<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @copyright   2015 - 2022  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshome.page/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests;

use Aviat\Ion\Di\{ContainerAware, ContainerInterface};
use Aviat\Ion\Json;

use Laminas\Diactoros\{
	Response as HttpResponse,
	ServerRequestFactory
};
use PHPUnit\Framework\TestCase;
use Spatie\Snapshots\MatchesSnapshots;
use function Aviat\Ion\_dir;
use function call_user_func_array;

use const Aviat\AnimeClient\{
	DEFAULT_CONTROLLER,
	SLUG_PATTERN,
};

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

	public static function setUpBeforeClass(): void
	{
		// Remove test cache files
		$files = glob(_dir(self::TEST_DATA_DIR, 'cache', '*.json'));
		array_map('unlink', $files);
	}

	protected function setUp(): void
	{
		parent::setUp();

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
			'routes' => [],
		];

		// Set up DI container
		$di = require self::ROOT_DIR . '/app/bootstrap.php';
		$container = $di($config_array);

		// Use mock session handler
		$container->set('session-handler', static function (): \Aviat\AnimeClient\Tests\TestSessionHandler {
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
