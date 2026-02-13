<?php declare(strict_types=1);
/**
 * Global setup for unit tests
 */

// Work around the silly timezone error
date_default_timezone_set('UTC');

define('AC_TEST_ROOT_DIR', dirname(__DIR__) . '/');
const SRC_DIR = AC_TEST_ROOT_DIR . 'src/';
const TEST_DIR = __DIR__ . '/';

// -----------------------------------------------------------------------------
// Autoloading
// -----------------------------------------------------------------------------

require_once AC_TEST_ROOT_DIR . 'vendor/autoload.php';

DG\BypassFinals::enable();

require_once TEST_DIR . 'AnimeClient/AnimeClientTestCase.php';

// -----------------------------------------------------------------------------
// Ini Settings
// -----------------------------------------------------------------------------
ini_set('session.use_cookies', '0');
// Start session here to suppress error about headers not sent
session_start();

// -----------------------------------------------------------------------------
// Load base test case and mocks
// -----------------------------------------------------------------------------

// Pre-define some superglobals
$_SESSION = [];
$_COOKIE = [];

// Request base test case and mocks
require_once TEST_DIR . 'AnimeClient/mocks.php';
require_once TEST_DIR . 'Ion/mocks.php';

// End of bootstrap.php
