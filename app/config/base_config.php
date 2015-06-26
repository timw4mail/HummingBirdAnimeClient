<?php
// ----------------------------------------------------------------------------
// Lower level configuration
//
// You shouldn't generally need to change anything below this line
// ----------------------------------------------------------------------------
$base_config = [
	// Cache paths
	'data_cache_path' => _dir(APP_DIR, 'cache'),
	'img_cache_path' => _dir(ROOT_DIR, 'public/images'),

	// Included config files
	'routes' => require _dir(CONF_DIR, 'routes.php'),
	'database' => require _dir(CONF_DIR, 'database.php'),
];