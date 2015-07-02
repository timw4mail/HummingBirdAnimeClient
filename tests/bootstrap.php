<?php
/**
 * Global setup for unit tests
 */

use \AnimeClient\Config;

// -----------------------------------------------------------------------------
// Mock the default error handler
// -----------------------------------------------------------------------------

class MockErrorHandler {
	public function addDataTable($name, Array $values) {}
}

$defaultHandler = new MockErrorHandler();

// -----------------------------------------------------------------------------
// Define a base testcase class
// -----------------------------------------------------------------------------

/**
 * Base class for TestCases
 */
class AnimeClient_TestCase extends PHPUnit_Framework_TestCase {

	protected $config;

	public function setUp()
	{
		parent::setUp();

		global $config;
		$this->config = new Config([
			'config' => [
				'asset_path' => '//localhost/assets/'
			],
			'base_config' => [
				'databaase' => [],
				'routes' => [
					'common' => [],
					'anime' => [],
					'manga' => []
				]
			]
		]);
		$config =& $this->config;
	}
}

// -----------------------------------------------------------------------------
// Autoloaders
// -----------------------------------------------------------------------------

// Define WHOSE constant
define('WHOSE', "Foo's");

// Define base path constants
define('ROOT_DIR', realpath(__DIR__ . DIRECTORY_SEPARATOR . "/../"));
require ROOT_DIR . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'base' . DIRECTORY_SEPARATOR . 'pre_conf_functions.php';
define('APP_DIR', _dir(ROOT_DIR, 'app'));
define('CONF_DIR', _dir(APP_DIR, 'config'));
define('BASE_DIR', _dir(APP_DIR, 'base'));

// Setup autoloaders
_setup_autoloaders();
require(_dir(BASE_DIR, 'functions.php'));

// Pre-define some superglobals
$_SESSION = [];
$_COOKIE = [];

// End of bootstrap.php