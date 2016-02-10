<?php
/**
 * Hummingbird Anime Client
 *
 * An API client for Hummingbird to manage anime and manga watch lists
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren
 * @copyright   Copyright (c) 2015 - 2016
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 * @license     MIT
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
		'sort_tables.js',
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