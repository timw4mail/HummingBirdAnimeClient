<?php
/**
 * Hummingbird Anime Client
 *
 * An API client for Hummingbird to manage anime and manga watch lists
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren
 * @copyright   Copyright (c) 2015 - 2016
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 * @license     MIT
 */

use Aviat\AnimeClient\AnimeClient;

return [
	// -------------------------------------------------------------------------
	// Routing options
	//
	// Specify default paths and views
	// -------------------------------------------------------------------------
	'route_config' => [
		// Subfolder prefix for url, if in a subdirectory of the web root
		'subfolder_prefix' => '',

		// Path to public directory, where images/css/javascript are located,
		// appended to the url
		'asset_path' => '/public',

		// Which list should be the default?
		'default_list' => 'anime', // anime or manga

		// Default pages for anime/manga
		'default_anime_list_path' => "watching", // watching|plan_to_watch|on_hold|dropped|completed|all
		'default_manga_list_path' => "reading", // reading|plan_to_read|on_hold|dropped|completed|all

		// Default view type (cover_view/list_view)
		'default_view_type' => 'cover_view',
	],
	// -------------------------------------------------------------------------
	// Routing Config
	//
	// Maps paths to controlers and methods
	// -------------------------------------------------------------------------
	'routes' => [
		// ---------------------------------------------------------------------
		// Anime List Routes
		// ---------------------------------------------------------------------
		'anime_add_form' => [
			'path' => '/anime/add',
			'action' => 'add_form',
			'verb' => 'get'
		],
		'anime_add' => [
			'path' => '/anime/add',
			'action' => 'add',
			'verb' => 'post'
		],
		// ---------------------------------------------------------------------
		// Anime Collection Routes
		// ---------------------------------------------------------------------
		'collection_search' => [
			'path' => '/collection/search',
			'action' => 'search'
		],
		'collection_add_form' => [
			'path' => '/collection/add',
			'action' => 'form',
			'params' => [],
		],
		'collection_edit_form' => [
			'path' => '/collection/edit/{id}',
			'action' => 'form',
			'tokens' => [
				'id' => '[0-9]+'
			]
		],
		'collection_add' => [
			'path' => '/collection/add',
			'action' => 'add',
			'verb' => 'post'
		],
		'collection_edit' => [
			'path' => '/collection/edit',
			'action' => 'edit',
			'verb' => 'post'
		],
		'collection' => [
			'path' => '/collection/view{/view}',
			'action' => 'index',
			'params' => [],
			'tokens' => [
				'view' => '[a-z_]+'
			]
		],
		// ---------------------------------------------------------------------
		// Default / Shared routes
		// ---------------------------------------------------------------------
		'login' => [
			'path' => '/{controller}/login',
			'action' => 'login',
		],
		'login_post' => [
			'path' => '/{controller}/login',
			'action' => 'login_action',
			'verb' => 'post'
		],
		'logout' => [
			'path' => '/{controller}/logout',
			'action' => 'logout'
		],
		'update' => [
			'path' => '/{controller}/update',
			'action' => 'update',
			'verb' => 'post',
			'tokens' => [
				'controller' => '[a-z_]+'
			]
		],
		'update_form' => [
			'path' => '/{controller}/update_form',
			'action' => 'form_update',
			'verb' => 'post',
			'tokens' => [
				'controller' => '[a-z_]+'
			]
		],
		'edit' => [
			'path' => '/{controller}/edit/{id}/{status}',
			'action' => 'edit',
			'tokens' => [
				'id' => '[0-9a-z_]+',
				'status' => '[a-zA-z\- ]+',
			]
		],
		'list' => [
			'path' => '/{controller}/{type}{/view}',
			'action' => AnimeClient::DEFAULT_CONTROLLER_METHOD,
			'tokens' => [
				'type' => '[a-z_]+',
				'view' => '[a-z_]+'
			]
		],
		'index_redirect' => [
			'path' => '/',
			'controller' => AnimeClient::DEFAULT_CONTROLLER_NAMESPACE,
			'action' => 'redirect_to_default'
		],
	]
];