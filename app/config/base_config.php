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

// ----------------------------------------------------------------------------
// Lower level configuration
//
// You shouldn't generally need to change anything below this line
// ----------------------------------------------------------------------------
$APP_DIR = realpath(__DIR__ . '/../');
$ROOT_DIR = realpath("{$APP_DIR}/../");

$base_config = [
	'asset_dir' => "{$ROOT_DIR}/public",

	// Template file path
	'view_path' => "{$APP_DIR}/views",

	// Cache paths
	'data_cache_path' => "{$APP_DIR}/cache",
	'img_cache_path' => "{$ROOT_DIR}/public/images",

	// Included config files
	'menus' => require 'menus.php',
	'routes' => require 'routes.php',
];