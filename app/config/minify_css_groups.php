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
 * This is the config array for css files to concatenate and minify
 */
return [
	/*-----
			Css
					-----*/

	/*
		For each group create an array like so

		'my_group' => array(
			'path/to/css/file1.css',
			'path/to/css/file2.css'
		),
	*/
	'base' => [
		'marx.css',
		'base.css'
	]
];
// End of css_groups.php