<?php
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
	'show_manga_collection' => TRUE,

	// path to public directory on the server
	'asset_dir' => realpath(__DIR__ . '/../../public'),

	// ----------------------------------------------------------------------------
	// Included config files
	// ----------------------------------------------------------------------------

	// Routing paths and options
	'routing' => require __DIR__ . '/routing.php',
];