<?php
/**
 * Hummingbird Anime Client
 *
 * An API client for Hummingbird to manage anime and manga watch lists
 *
 * PHP version 5.6
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2016  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     3.1
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

use Aviat\AnimeClient\AnimeClient;

return [
	// -------------------------------------------------------------------------
	// Routing options
	//
	// Specify default paths and views
	// -------------------------------------------------------------------------
	'route_config' => [
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
		'anime.add.get' => [
			'path' => '/anime/add',
			'action' => 'add_form',
			'verb' => 'get',
		],
		'anime.add.post' => [
			'path' => '/anime/add',
			'action' => 'add',
			'verb' => 'post',
		],
		'anime.details' => [
			'path' => '/anime/details/{id}',
			'action' => 'details',
			'tokens' => [
				'id' => '[a-z0-9\-]+',
			],
		],
		'anime.delete' => [
			'path' => '/anime/delete',
			'action' => 'delete',
			'verb' => 'post',
		],
		// ---------------------------------------------------------------------
		// Manga Routes
		// ---------------------------------------------------------------------
		'manga.search' => [
			'path' => '/manga/search',
			'action' => 'search',
		],
		'manga.add.get' => [
			'path' => '/manga/add',
			'action' => 'add_form',
			'verb' => 'get',
		],
		'manga.add.post' => [
			'path' => '/manga/add',
			'action' => 'add',
			'verb' => 'post',
		],
		'manga.delete' => [
			'path' => '/manga/delete',
			'action' => 'delete',
			'verb' => 'post',
		],
		'manga.details' => [
			'path' => '/manga/details/{id}',
			'action' => 'details',
			'tokens' => [
				'id' => '[a-z0-9\-]+',
			],
		],
		// ---------------------------------------------------------------------
		// Anime Collection Routes
		// ---------------------------------------------------------------------
		'collection.search' => [
			'path' => '/collection/search',
			'action' => 'search',
		],
		'collection.add.get' => [
			'path' => '/collection/add',
			'action' => 'form',
			'params' => [],
		],
		'collection.edit.get' => [
			'path' => '/collection/edit/{id}',
			'action' => 'form',
			'tokens' => [
				'id' => '[0-9]+',
			],
		],
		'collection.add.post' => [
			'path' => '/collection/add',
			'action' => 'add',
			'verb' => 'post',
		],
		'collection.edit.post' => [
			'path' => '/collection/edit',
			'action' => 'edit',
			'verb' => 'post',
		],
		'collection.view' => [
			'path' => '/collection/view{/view}',
			'action' => 'index',
			'params' => [],
			'tokens' => [
				'view' => '[a-z_]+',
			],
		],
		'collection.delete' => [
			'path' => '/collection/delete',
			'action' => 'delete',
			'verb' => 'post',
		],
		// ---------------------------------------------------------------------
		// Manga Collection Routes
		// ---------------------------------------------------------------------
		// ---------------------------------------------------------------------
		// Default / Shared routes
		// ---------------------------------------------------------------------
		'cache_purge' => [
			'path' => '/cache_purge',
			'action' => 'clear_cache',
			'controller' => AnimeClient::DEFAULT_CONTROLLER_NAMESPACE,
			'verb' => 'get',
		],
		'login' => [
			'path' => '/login',
			'action' => 'login',
			'controller' => AnimeClient::DEFAULT_CONTROLLER_NAMESPACE,
			'verb' => 'get',
		],
		'login.post' => [
			'path' => '/login',
			'action' => 'login_action',
			'controller' => AnimeClient::DEFAULT_CONTROLLER_NAMESPACE,
			'verb' => 'post',
		],
		'logout' => [
			'path' => '/logout',
			'action' => 'logout',
			'controller' => AnimeClient::DEFAULT_CONTROLLER_NAMESPACE,
		],
		'update' => [
			'path' => '/{controller}/update',
			'action' => 'update',
			'verb' => 'post',
			'tokens' => [
				'controller' => '[a-z_]+',
			],
		],
		'update.post' => [
			'path' => '/{controller}/update_form',
			'action' => 'form_update',
			'verb' => 'post',
			'tokens' => [
				'controller' => '[a-z_]+',
			],
		],
		'edit' => [
			'path' => '/{controller}/edit/{id}/{status}',
			'action' => 'edit',
			'tokens' => [
				'id' => '[0-9a-z_]+',
				'status' => '([a-zA-Z\-_]|%20)+',
			],
		],
		'list' => [
			'path' => '/{controller}/{type}{/view}',
			'action' => AnimeClient::DEFAULT_CONTROLLER_METHOD,
			'tokens' => [
				'type' => '[a-z_]+',
				'view' => '[a-z_]+',
			],
		],
		'index_redirect' => [
			'path' => '/',
			'controller' => AnimeClient::DEFAULT_CONTROLLER_NAMESPACE,
			'action' => 'redirect_to_default',
		],
	],
];