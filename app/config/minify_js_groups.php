<?php
/**
 * Hummingbird Anime Client
 *
 * An API client for Hummingbird to manage anime and manga watch lists
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren
 * @copyright   Copyright (c) 2015
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 * @license     MIT
 */

// --------------------------------------------------------------------------

/**
 * This is the config array for javascript files to concatenate and minify
 */
return [
	/*
		For each group create an array like so

		'my_group' => array(
			'path/to/js/file1.js',
			'path/to/js/file2.js'
		),
	*/
	'table' => [
		'lib/jquery.min.js',
		'lib/table_sorter/jquery.tablesorter.min.js',
		'sort_tables.js'
	],
	'edit' => [
		'lib/jquery.min.js',
		'show_message.js',
		'anime_edit.js',
		'manga_edit.js'
	],
	'collection' => [
		'lib/jquery.min.js',
		'lib/jquery.throttle-debounce.js',
		'lib/jsrender.js',
		'collection.js'
	]
];

// End of js_groups.php