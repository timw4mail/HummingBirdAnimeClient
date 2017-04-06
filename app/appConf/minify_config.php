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

// --------------------------------------------------------------------------

return [

	/*
	|--------------------------------------------------------------------------
	| JS Folder
	|--------------------------------------------------------------------------
	|
	| The folder where javascript files exist, in relation to the document root
	|
	*/
	'js_root' => 'js/',

	 /*
	|--------------------------------------------------------------------------
	| JS Groups
	|--------------------------------------------------------------------------
	|
	| Config array for javascript files to concatenate and minify
	|
	*/
	'groups' => [
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
	]
];
// End of minify_config.php