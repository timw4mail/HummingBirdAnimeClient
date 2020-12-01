<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.1
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient;

use Aviat\AnimeClient\Types\Config as ConfigType;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

use function Aviat\Ion\_dir;

setlocale(LC_CTYPE, 'en_US');

// Load composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

if (file_exists('.is-dev'))
{
	$whoops = new Run;
	$whoops->pushHandler(new PrettyPageHandler);
	$whoops->register();
}

// Define base directories
$APP_DIR = _dir(__DIR__, 'app');
$APPCONF_DIR = _dir($APP_DIR, 'appConf');
$CONF_DIR = _dir($APP_DIR, 'config');

// -----------------------------------------------------------------------------
// Dependency Injection setup
// -----------------------------------------------------------------------------
$baseConfig = require "{$APPCONF_DIR}/base_config.php";
$di = require "{$APP_DIR}/bootstrap.php";

$config = loadToml($CONF_DIR);

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
if (array_key_exists('timezone', $checkedConfig) && ! empty($checkedConfig['timezone']))
{
	date_default_timezone_set($checkedConfig['timezone']);
}
else if ($timezone !== '')
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