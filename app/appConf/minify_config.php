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

return [

	/*
	|--------------------------------------------------------------------------
	| CSS Folder
	|--------------------------------------------------------------------------
	|
	| The folder where css files exist, in relation to the document root
	|
	*/
	'css_root' => 'css/',

	/*
	|--------------------------------------------------------------------------
	| Path from
	|--------------------------------------------------------------------------
	|
	| Path fragment to rewrite in css files
	|
	*/
	'path_from' => '',

	/*
	|--------------------------------------------------------------------------
	| Path to
	|--------------------------------------------------------------------------
	|
	| The path fragment replacement for the css files
	|
	*/
	'path_to' => '',

	/*
	|--------------------------------------------------------------------------
	| CSS Groups file
	|--------------------------------------------------------------------------
	|
	| The file where the css groups are configured
	|
	*/
	'css_groups_file' => __DIR__ . '/minify_css_groups.php',

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
	| JS Groups file
	|--------------------------------------------------------------------------
	|
	| The file where the javascript groups are configured
	|
	*/
	'js_groups_file' => __DIR__ . '/minify_js_groups.php',

];
// End of minify_config.php