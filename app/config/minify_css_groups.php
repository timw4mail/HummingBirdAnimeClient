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
		'base.css'
	]
];
// End of css_groups.php