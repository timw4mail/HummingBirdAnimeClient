<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2021  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

use function Aviat\AnimeClient\loadConfig;

// ----------------------------------------------------------------------------
// Lower level configuration
//
// You shouldn't generally need to change anything below this line
// ----------------------------------------------------------------------------
$APP_DIR = dirname(__DIR__);
$ROOT_DIR = dirname($APP_DIR);

$tomlConfig = loadConfig(__DIR__);

return array_merge($tomlConfig, [
	'root' => $ROOT_DIR,
	'asset_dir' => "{$ROOT_DIR}/public",
	'base_config_dir' => __DIR__,
	'config_dir' => "{$APP_DIR}/config",

	// No config defaults
	'kitsu_username' => 'timw4mail',
	'whose_list' => 'Someone',
	'cache' => [
		'connection' => [],
		'driver' => 'null',
	],
	'secure_urls' => TRUE,

	// Routing defaults
	'asset_path' => '/public',
	'default_list' => 'anime', //anime|manga
	'default_anime_list_path' => 'watching', // watching|plan_to_watch|on_hold|dropped|completed|all
	'default_manga_list_path' => 'reading', // reading|plan_to_read|on_hold|dropped|completed|all
	'default_view_type' => 'cover_view', // cover_view|list_view

	// Template file path
	'view_path' => "{$APP_DIR}/views",

	// Cache paths
	'data_cache_path' => "{$APP_DIR}/cache",
	'img_cache_path' => "{$ROOT_DIR}/public/images",

	// Included config files
	'routes' => require 'routes.php',
]);
