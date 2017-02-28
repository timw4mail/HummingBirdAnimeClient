<?php
/**
 * Global setup for unit tests
 */

// Work around the silly timezone error
$timezone = ini_get('date.timezone');
if ($timezone === '' || $timezone === FALSE)
{
	ini_set('date.timezone', 'GMT');
}

// -----------------------------------------------------------------------------
// Autoloading
// -----------------------------------------------------------------------------

require __DIR__ . '/AnimeClientTestCase.php';
require __DIR__ . '/../vendor/autoload.php';

// -----------------------------------------------------------------------------
// Ini Settings
// -----------------------------------------------------------------------------
ini_set('session.use_cookies', 0);
ini_set("session.use_only_cookies", 0);
ini_set("session.use_trans_sid", 1);
// Start session here to supress error about headers not sent
session_start();

// -----------------------------------------------------------------------------
// Load base test case and mocks
// -----------------------------------------------------------------------------

// Pre-define some superglobals
$_SESSION = [];
$_COOKIE = [];

// Request base test case and mocks
require __DIR__ . '/TestSessionHandler.php';
require __DIR__ . '/mocks.php';

// End of bootstrap.php