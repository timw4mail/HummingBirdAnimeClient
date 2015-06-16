<?php
return (object)[
	// ----------------------------------------------------------------------------
	// Username for anime and manga lists
	// ----------------------------------------------------------------------------
	'hummingbird_username' => 'timw4mail',

	// ----------------------------------------------------------------------------
	// General config
	// ----------------------------------------------------------------------------

	// do you wish to show the anime collection tab?
	'show_anime_collection' => FALSE,

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

	// ----------------------------------------------------------------------------
	// Lower level configuration
	//
	// You shouldn't generally need to change anything below this line
	// ----------------------------------------------------------------------------

	// Cache paths
	'data_cache_path' => _dir(APP_DIR, 'cache'),
	'img_cache_path' => _dir(ROOT_DIR, 'public/images'),

	// Included config files
	'routes' => require _dir(CONF_DIR, 'routes.php'),
	'database' => require _dir(CONF_DIR, 'database.php'),
];