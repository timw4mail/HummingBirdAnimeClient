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
// Composer autoload
require realpath(__DIR__ . '/../vendor/autoload.php');
require 'Ion_TestCase.php';

// -----------------------------------------------------------------------------
// Ini Settings
// -----------------------------------------------------------------------------
ini_set('session.use_cookies', 0);
ini_set('session.use_only_cookies',0);
ini_set('session.use_trans_sid',1);
// Start session here to surpress error about headers not sent
session_start();

// -----------------------------------------------------------------------------
// Load base test case and mocks
// -----------------------------------------------------------------------------

// Pre-define some superglobals
$_SESSION = [];
$_COOKIE = [];

// Request base test case and mocks
require 'TestSessionHandler.php';
require 'mocks.php';

// End of bootstrap.php