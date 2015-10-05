<?php
// ----------------------------------------------------------------------------
// Lower level configuration
//
// You shouldn't generally need to change anything below this line
// ----------------------------------------------------------------------------
$base_config = [
	// Template file path
	'view_path' => _dir(APP_DIR, 'views'),

	// Cache paths
	'data_cache_path' => _dir(APP_DIR, 'cache'),
	'img_cache_path' => _dir(ROOT_DIR, 'public/images'),

	// Included config files
	'database' => require __DIR__ . '/database.php',
	'menus' => require __DIR__ . '/menus.php',
	'routes' => require __DIR__ . '/routes.php',
];