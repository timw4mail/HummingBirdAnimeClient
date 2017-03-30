<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2017  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient;

use function Aviat\AnimeClient\loadToml;

use Aviat\AnimeClient\AnimeClient;

// Work around the silly timezone error
$timezone = ini_get('date.timezone');
if ($timezone === '' || $timezone === FALSE)
{
	ini_set('date.timezone', 'GMT');
}

// Load composer autoloader
require __DIR__ . '/vendor/autoload.php';

// Define base directories
$APP_DIR = _dir(__DIR__, 'app');
$APPCONF_DIR = _dir($APP_DIR, 'appConf');
$CONF_DIR = _dir($APP_DIR, 'config');

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