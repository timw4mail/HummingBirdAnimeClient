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

// --------------------------------------------------------------------------

/**
 * This is the config array for javascript files to concatenate and minify
 */
return [
	'base' => [
		'base/classList.js',
		'base/AnimeClient.js',
	],
	'event' => [
		'base/events.js',
	],
	'table' => [
		'base/sort_tables.js',
	],
	'table_edit' => [
		'base/sort_tables.js',
		'anime_edit.js',
		'manga_edit.js',
	],
	'edit' => [
		'anime_edit.js',
		'manga_edit.js',
	],
	'anime_collection' => [
		'lib/mustache.js',
		'anime_collection.js',
	],
	'manga_collection' => [
		'lib/mustache.js',
		'manga_collection.js',
	],
];

// End of js_groups.php