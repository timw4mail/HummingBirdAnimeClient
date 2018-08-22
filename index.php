<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2018  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient;

use function Aviat\Ion\_dir;

// Work around the silly timezone error
$timezone = ini_get('date.timezone');
if ($timezone === '' || $timezone === FALSE)
{
	ini_set('date.timezone', 'GMT');
}

// Load composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// if (array_key_exists('ENV', $_ENV) && $_ENV['ENV'] === 'development')
{
	$whoops = new \Whoops\Run;
	$whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
	$whoops->register();
}

// Define base directories
$APP_DIR = _dir(__DIR__, 'app');
$APPCONF_DIR = _dir($APP_DIR, 'appConf');
$CONF_DIR = _dir($APP_DIR, 'config');

// -----------------------------------------------------------------------------
// Dependency Injection setup
// -----------------------------------------------------------------------------
$base_config = require $APPCONF_DIR . '/base_config.php';
$di = require $APP_DIR . '/bootstrap.php';

$config = loadToml($CONF_DIR);
$config_array = array_merge($base_config, $config);
// User config
$config_array['default_config'] = $base_config;
$config_array['user_config_settings'] = $config;

$container = $di($config_array);

// Unset 'constants'
unset($APP_DIR, $APPCONF_DIR);

// -----------------------------------------------------------------------------
// Dispatch to the current route
// -----------------------------------------------------------------------------
$container->get('dispatcher')();