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
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

use function Aviat\AnimeClient\loadToml;

// ----------------------------------------------------------------------------
// Lower level configuration
//
// You shouldn't generally need to change anything below this line
// ----------------------------------------------------------------------------
$APP_DIR = realpath(__DIR__ . '/../');
$ROOT_DIR = realpath("{$APP_DIR}/../");

$tomlConfig = loadToml(__DIR__);

return array_merge($tomlConfig, [
	'asset_dir' => "{$ROOT_DIR}/public",

	// Template file path
	'view_path' => "{$APP_DIR}/views",

	// Cache paths
	'data_cache_path' => "{$APP_DIR}/cache",
	'img_cache_path' => "{$ROOT_DIR}/public/images",

	// Included config files
	'routes' => require 'routes.php',
]);