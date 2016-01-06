<?php

use Aura\Web\WebFactory;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

use Aviat\AnimeClient\Config;

/**
 * Base class for TestCases
 */
class AnimeClient_TestCase extends PHPUnit_Framework_TestCase {
	protected $container;
	protected static $staticContainer;
	protected static $session_handler;

	public static function setUpBeforeClass()
	{
		// Use mock session handler
		$session_handler = new TestSessionHandler();
		session_set_save_handler($session_handler, TRUE);
		self::$session_handler = $session_handler;

		// Remove test cache files
		$files = glob(_dir(TEST_DATA_DIR, 'cache', '*.json'));
		array_map('unlink', $files);
	}

	public function setUp()
	{
		parent::setUp();

		$config_array = [
			'asset_path' => '//localhost/assets/',
			'img_cache_path' => _dir(ROOT_DIR, 'public/images'),
			'data_cache_path' => _dir(TEST_DATA_DIR, 'cache'),
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
				]
			],
			'routes' => [
				'route_config' => [
					'asset_path' => '/assets'
				],
				'routes' => [

				]
			]
		];

		// Set up DI container
		$di = require _dir(APP_DIR, 'bootstrap.php');
		$container = $di($config_array);
		$container->set('error-handler', new MockErrorHandler());
		$container->set('session-handler', self::$session_handler);

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
			'_GET' => $_GET,
			'_POST' => $_POST,
			'_COOKIE' => $_COOKIE,
			'_SERVER' => $_SERVER,
			'_FILES' => $_FILES
		];
		$web_factory = new WebFactory(array_merge($default,$supers));
		$this->container->set('request', $web_factory->newRequest());
		$this->container->set('response', $web_factory->newResponse());
	}

	/**
	 * Create a mock guzzle client for testing
	 * api call methods
	 *
	 * @param  int $code The status code
	 * @param  array $headers
	 * @param  string $body
	 * @return Client
	 */
	public function getMockClient($code, $headers, $body)
	{
		$mock = new MockHandler([
			new Response($code, $headers, $body)
		]);
		$handler = HandlerStack::create($mock);
		$client = new Client(['handler' => $handler]);

		return $client;
	}
}
// End of AnimeClient_TestCase.php