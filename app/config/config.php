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

$config = [
	// ----------------------------------------------------------------------------
	// Username for anime and manga lists
	// ----------------------------------------------------------------------------
	'hummingbird_username' => 'timw4mail',

	// ----------------------------------------------------------------------------
	// Whose list is it?
	// ----------------------------------------------------------------------------
	'whose_list' => 'Tim',

	// ----------------------------------------------------------------------------
	// General config
	// ----------------------------------------------------------------------------

	// do you wish to show the anime collection?
	'show_anime_collection' => TRUE,

	// do you wish to show the manga collection?
	'show_manga_collection' => FALSE,

	// path to public directory on the server
	'asset_dir' => realpath(__DIR__ . '/../../public'),

	// ----------------------------------------------------------------------------
	// Included config files
	// ----------------------------------------------------------------------------

	// Routing paths and options
	'routing' => require __DIR__ . '/routing.php',
];