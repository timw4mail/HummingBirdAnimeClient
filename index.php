<?php
/**
 * Hummingbird Anime Client
 *
 * An API client for Hummingbird to manage anime and manga watch lists
 *
 * PHP version 5.6
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2016  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     3.1
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */
namespace Aviat\AnimeClient;

use function Aviat\AnimeClient\loadToml;

use Aviat\AnimeClient\AnimeClient;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

// Work around the silly timezone error
$timezone = ini_get('date.timezone');
if ($timezone === '' || $timezone === FALSE)
{
	ini_set('date.timezone', 'GMT');
}

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

// Define base directories
$APP_DIR = _dir(__DIR__, 'app');
$APPCONF_DIR = _dir($APP_DIR, 'appConf');
$CONF_DIR = _dir($APP_DIR, 'config');

// Load composer autoloader
require _dir(__DIR__, 'vendor/autoload.php');

// -------------------------------------------------------------------------
// Setup error handling
// -------------------------------------------------------------------------
$whoops = new Run();

// Set up default handler for general errors
$defaultHandler = new PrettyPageHandler();
$whoops->pushHandler($defaultHandler);

// Register as the error handler
$whoops->register();

// -----------------------------------------------------------------------------
// Dependency Injection setup
// -----------------------------------------------------------------------------
require _dir($APPCONF_DIR, 'base_config.php'); // $base_config
$di = require _dir($APP_DIR, 'bootstrap.php');

$config = loadToml($CONF_DIR);
$config_array = array_merge($base_config, $config);

$container = $di($config_array);

// Unset 'constants'
unset($APP_DIR);
unset($CONF_DIR);

// -----------------------------------------------------------------------------
// Dispatch to the current route
// -----------------------------------------------------------------------------
$container->get('dispatcher')->__invoke();