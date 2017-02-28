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