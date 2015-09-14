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
	'asset_dir' => __DIR__ . '/../../public',

	// ----------------------------------------------------------------------------
	// Routing
	// ----------------------------------------------------------------------------

	'routing' => [
		// Subfolder prefix for url
		'subfolder_prefix' => '',

		// Path to public directory, where images/css/javascript are located,
		// appended to the url
		'asset_path' => '/public',

		// Url paths to each content type
		'anime_path' => 'anime',
		'manga_path' => 'manga',
		'collection_path' => 'collection',
		'stats_path' => 'stats',

		// Which list should be the default?
		'default_list' => 'anime', // anime or manga

		// Default pages for anime/manga
		'default_anime_path' => "/anime/watching",
		'default_manga_path' => '/manga/all',

		// Default to list view?
		'default_to_list_view' => FALSE,
	],

	// Url paths to each
	'anime_path' => 'anime',
	'manga_path' => 'manga',
	'collection_path' => 'collection',
	'stats_path' => 'stats',

	// Which list should be the default?
	'default_list' => 'anime', // anime or manga

	// Default pages for anime/manga
	'default_anime_path' => "/anime/watching",
	'default_manga_path' => '/manga/all',

	// Default to list view?
	'default_to_list_view' => FALSE,
];