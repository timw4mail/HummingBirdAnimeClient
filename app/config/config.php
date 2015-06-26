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

	// path to public directory
	'asset_path' => '//' . $_SERVER['HTTP_HOST'] . '/public',

	// path to public directory on the server
	'asset_dir' => __DIR__ . '/../../public',

	// ----------------------------------------------------------------------------
	// Routing
	//
	// Route by path, or route by domain. To route by path, set the _host suffixed
	// options to an empty string. To route by host, set the _path suffixed options
	// to an empty string
	// ----------------------------------------------------------------------------

	'anime_host' => 'anime.timshomepage.net',
	'manga_host' => 'manga.timshomepage.net',
	'anime_path' => '',
	'manga_path' => '',

	// Default pages for anime/manga
	'default_anime_path' => '/watching',
	'default_manga_path' => '/all',

	// Default to list view?
	'default_to_list_view' => FALSE,
];