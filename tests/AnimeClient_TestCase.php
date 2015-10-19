<?php

use Aura\Web\WebFactory;

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
	}

	public function setUp()
	{
		parent::setUp();
		
		$config_array = [
			'asset_path' => '//localhost/assets/',
			'databaase' => [],
			'routing' => [

			],
			'routes' => [
				'convention' => [
					'default_controller' => '',
					'default_method' => '',
				],
				'common' => [],
				'anime' => [],
				'manga' => []
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
}
// End of AnimeClient_TestCase.php