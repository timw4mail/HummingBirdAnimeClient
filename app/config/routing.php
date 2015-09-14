<?php

// ----------------------------------------------------------------------------
// Routing
// ----------------------------------------------------------------------------

return [
	// Subfolder prefix for url
	'subfolder_prefix' => '',

	// Path to public directory, where images/css/javascript are located,
	// appended to the url
	'asset_path' => '/public',

	// Which list should be the default?
	'default_list' => 'anime', // anime or manga

	// Default pages for anime/manga
	'default_anime_path' => "/anime/watching",
	'default_manga_path' => '/manga/all',

	// Default to list view?
	'default_to_list_view' => FALSE,
];