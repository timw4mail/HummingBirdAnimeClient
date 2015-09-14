<?php
/**
 * Global setup for unit tests
 */

use Aviat\AnimeClient\Base\Config;
use Aviat\AnimeClient\Base\Container;

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
	protected $container;

	public function setUp()
	{
		parent::setUp();

		$config = new Config([
			'asset_path' => '//localhost/assets/',
			'databaase' => [],
			'routes' => [
				'common' => [],
				'anime' => [],
				'manga' => []
			]
		]);

		$container = new Container([
			'config' => $config
		]);

		$this->container = $container;
	}
}

// -----------------------------------------------------------------------------
// Autoloaders
// -----------------------------------------------------------------------------

// Define WHOSE constant
define('WHOSE', "Foo's");

// Define base path constants
define('ROOT_DIR', realpath(__DIR__ . DIRECTORY_SEPARATOR . "/../"));

/**
 * Joins paths together. Variadic to take an
 * arbitrary number of arguments
 *
 * @return string
 */
function _dir()
{
	return implode(DIRECTORY_SEPARATOR, func_get_args());
}

define('APP_DIR', _dir(ROOT_DIR, 'app'));
define('CONF_DIR', _dir(APP_DIR, 'config'));
define('SRC_DIR', _dir(ROOT_DIR, 'src'));
define('BASE_DIR', _dir(SRC_DIR, 'Base'));

/**
 * Set up autoloaders
 *
 * @codeCoverageIgnore
 * @return void
 */
function _setup_autoloaders()
{
	require _dir(ROOT_DIR, '/vendor/autoload.php');
	spl_autoload_register(function ($class) {
		$class_parts = explode('\\', $class);
		$ns_path = SRC_DIR . '/' . implode('/', $class_parts) . ".php";

		if (file_exists($ns_path))
		{
			require_once($ns_path);
			return;
		}
	});
}

// Setup autoloaders
_setup_autoloaders();
require(_dir(SRC_DIR, 'functions.php'));

// Pre-define some superglobals
$_SESSION = [];
$_COOKIE = [];

// End of bootstrap.php