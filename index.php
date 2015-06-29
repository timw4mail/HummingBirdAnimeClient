<?php
/**
 * Here begins everything!
 */

namespace AnimeClient;

// -----------------------------------------------------------------------------
// ! Start config
// -----------------------------------------------------------------------------

/**
 * Well, whose list is it?
 */
define('WHOSE', "Tim's");

// -----------------------------------------------------------------------------
// ! End config
// -----------------------------------------------------------------------------

\session_start();

// Work around the silly timezone error
$timezone = ini_get('date.timezone');
if ($timezone === '' || $timezone === FALSE)
{
	ini_set('date.timezone', 'GMT');
}

// Define base directories
define('ROOT_DIR', __DIR__);
define('APP_DIR', ROOT_DIR . DIRECTORY_SEPARATOR . 'app');
define('CONF_DIR', APP_DIR . DIRECTORY_SEPARATOR . 'config');
define('BASE_DIR', APP_DIR . DIRECTORY_SEPARATOR . 'base');
require BASE_DIR . DIRECTORY_SEPARATOR . 'pre_conf_functions.php';

// Setup autoloaders
_setup_autoloaders();

// Do dependency injection, and go!
require _dir(APP_DIR, 'bootstrap.php');

// End of index.php