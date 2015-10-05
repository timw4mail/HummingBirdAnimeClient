<?php

// ----------------------------------------------------------------------------
// Routing
// ----------------------------------------------------------------------------

return [
	// Subfolder prefix for url, if in a subdirectory of the web root
	'subfolder_prefix' => '',

	// Path to public directory, where images/css/javascript are located,
	// appended to the url
	'asset_path' => '/public',

	// Which list should be the default?
	'default_list' => 'anime', // anime or manga

	// Default pages for anime/manga
	'default_anime_list_path' => "watching", // watching|plan_to_watch|on_hold|dropped|completed|all
	'default_manga_list_path' => "all", // reading|plan_to_read|on_hold|dropped|completed|all

	// Default view type (cover_view/list_view)
	'default_view_type' => 'cover_view',
];