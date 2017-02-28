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