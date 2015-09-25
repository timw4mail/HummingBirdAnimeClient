<?php
/**
 * Global setup for unit tests
 */

use Aviat\AnimeClient\Config;
use Aviat\Ion\Di\Container;
use Aviat\AnimeClient\UrlGenerator;

// -----------------------------------------------------------------------------
// Mock the default error handler
// -----------------------------------------------------------------------------

class MockErrorHandler {
	public function addDataTable($name, Array $values) {}
}

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
			'config' => $config,
			'error-handler' => new MockErrorHandler()
		]);

		$container->set('url-generator', new UrlGenerator($container));

		$this->container = $container;
	}
}

// -----------------------------------------------------------------------------
// Autoloaders
// -----------------------------------------------------------------------------

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

// Define base path constants
define('ROOT_DIR', realpath(_dir(__DIR__, "/../")));
define('APP_DIR', _dir(ROOT_DIR, 'app'));
define('CONF_DIR', _dir(APP_DIR, 'config'));
define('SRC_DIR', _dir(ROOT_DIR, 'src'));
define('BASE_DIR', _dir(SRC_DIR, 'Base'));
require _dir(ROOT_DIR, '/vendor/autoload.php');
require _dir(SRC_DIR, 'functions.php');

/**
 * Set up autoloaders
 *
 * @codeCoverageIgnore
 * @return void
 */
spl_autoload_register(function ($class) {
	$class_parts = explode('\\', $class);
	$ns_path = SRC_DIR . '/' . implode('/', $class_parts) . ".php";

	if (file_exists($ns_path))
	{
		require_once($ns_path);
		return;
	}
});

// Pre-define some superglobals
$_SESSION = [];
$_COOKIE = [];

// End of bootstrap.php
