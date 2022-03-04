<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshome.page>
 * @copyright   2015 - 2022  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient;

use Aviat\AnimeClient\Types\Config as ConfigType;
use Tracy\Debugger;
use function Aviat\Ion\_dir;

setlocale(LC_CTYPE, 'en_US');

// Load composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

Debugger::$strictMode = E_ALL & ~E_DEPRECATED; // all errors except deprecated notices
Debugger::$showBar = FALSE;
Debugger::enable(Debugger::DEVELOPMENT, __DIR__ . '/app/logs');

// Define base directories
$APP_DIR = _dir(__DIR__, 'app');
$APPCONF_DIR = _dir($APP_DIR, 'appConf');
$CONF_DIR = _dir($APP_DIR, 'config');

// -----------------------------------------------------------------------------
// Dependency Injection setup
// -----------------------------------------------------------------------------
$baseConfig = require "{$APPCONF_DIR}/base_config.php";
$di = require "{$APP_DIR}/bootstrap.php";

$config = loadConfig($CONF_DIR);

$overrideFile = "{$CONF_DIR}/admin-override.toml";
$overrideConfig = file_exists($overrideFile)
	? loadTomlFile($overrideFile)
	: [];

$configArray = array_replace_recursive($baseConfig, $config, $overrideConfig);

$checkedConfig = ConfigType::check($configArray);

// Set the timezone for date display
// First look in app config, then PHP config, and at last
// resort, just set to UTC.
$timezone = ini_get('date.timezone');
if (is_array($checkedConfig) && array_key_exists('timezone', $checkedConfig) && ! empty($checkedConfig['timezone']))
{
	date_default_timezone_set($checkedConfig['timezone']);
}
elseif (is_string($timezone) && $timezone !== '')
{
	date_default_timezone_set($timezone);
}
else
{
	date_default_timezone_set('UTC');
}

$container = $di($checkedConfig);

// Unset 'constants'
unset($APP_DIR, $CONF_DIR, $APPCONF_DIR);

// -----------------------------------------------------------------------------
// Dispatch to the current route
// -----------------------------------------------------------------------------
$container->get('dispatcher')();
