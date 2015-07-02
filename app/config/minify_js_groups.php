<?php
/**
 * Easy Min
 *
 * Simple minification for better website performance
 *
 * @author		Timothy J. Warren
 * @copyright	Copyright (c) 2012
 * @link 		https://github.com/aviat4ion/Easy-Min
 * @license		http://philsturgeon.co.uk/code/dbad-license
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