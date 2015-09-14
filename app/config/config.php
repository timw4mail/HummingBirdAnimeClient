<?php
$config = [
	// ----------------------------------------------------------------------------
	// Username for anime and manga lists
	// ----------------------------------------------------------------------------
	'hummingbird_username' => 'timw4mail',

	// ----------------------------------------------------------------------------
	// General config
	// ----------------------------------------------------------------------------

	// do you wish to show the anime collection tab?
	'show_anime_collection' => TRUE,

	// path to public directory on the server
	'asset_dir' => realpath(__DIR__ . '/../../public'),

	// ----------------------------------------------------------------------------
	// Included config files
	// ----------------------------------------------------------------------------

	// Routing paths and options
	'routing' => require __DIR__ . '/routing.php',
];