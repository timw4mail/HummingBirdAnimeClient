<?php
return (object)[
	// Username for feeds
	'hummingbird_username' => 'timw4mail',

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

	// Cache paths
	'data_cache_path' => _dir(APP_DIR, 'cache'),
	'img_cache_path' => _dir(ROOT_DIR, 'public/images'),

	// Included config files
	'routes' => require _dir(CONF_DIR, 'routes.php'),
	'database' => require _dir(CONF_DIR, 'database.php'),
];