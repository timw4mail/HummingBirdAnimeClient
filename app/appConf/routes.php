<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2018  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

use const Aviat\AnimeClient\{
	DEFAULT_CONTROLLER_METHOD,
	DEFAULT_CONTROLLER
};

// -------------------------------------------------------------------------
// Routing Config
//
// Maps paths to controllers and methods
// -------------------------------------------------------------------------
return [
	// ---------------------------------------------------------------------
	// Anime List Routes
	// ---------------------------------------------------------------------
	'anime.add.get' => [
		'path' => '/anime/add',
		'action' => 'addForm',
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
		'action' => 'addForm',
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
	'anime.collection.search' => [
		'path' => '/anime-collection/search',
		'action' => 'search',
	],
	'anime.collection.add.get' => [
		'path' => '/anime-collection/add',
		'action' => 'form',
		'params' => [],
	],
	'anime.collection.edit.get' => [
		'path' => '/anime-collection/edit/{id}',
		'action' => 'form',
		'tokens' => [
			'id' => '[0-9]+',
		],
	],
	'anime.collection.add.post' => [
		'path' => '/anime-collection/add',
		'action' => 'add',
		'verb' => 'post',
	],
	'anime.collection.edit.post' => [
		'path' => '/anime-collection/edit',
		'action' => 'edit',
		'verb' => 'post',
	],
	'anime.collection.view' => [
		'path' => '/anime-collection/view{/view}',
		'action' => 'index',
		'params' => [],
		'tokens' => [
			'view' => '[a-z_]+',
		],
	],
	'anime.collection.delete' => [
		'path' => '/anime-collection/delete',
		'action' => 'delete',
		'verb' => 'post',
	],
	// ---------------------------------------------------------------------
	// Manga Collection Routes
	// ---------------------------------------------------------------------
	'manga.collection.search' => [
		'path' => '/manga-collection/search',
		'action' => 'search',
	],
	'manga.collection.add.get' => [
		'path' => '/manga-collection/add',
		'action' => 'form',
		'params' => [],
	],
	'manga.collection.edit.get' => [
		'path' => '/manga-collection/edit/{id}',
		'action' => 'form',
		'tokens' => [
			'id' => '[0-9]+',
		],
	],
	'manga.collection.add.post' => [
		'path' => '/manga-collection/add',
		'action' => 'add',
		'verb' => 'post',
	],
	'manga.collection.edit.post' => [
		'path' => '/manga-collection/edit',
		'action' => 'edit',
		'verb' => 'post',
	],
	'manga.collection.view' => [
		'path' => '/manga-collection/view{/view}',
		'action' => 'index',
		'params' => [],
		'tokens' => [
			'view' => '[a-z_]+',
		],
	],
	'manga.collection.delete' => [
		'path' => '/manga-collection/delete',
		'action' => 'delete',
		'verb' => 'post',
	],
	// ---------------------------------------------------------------------
	// Other Routes
	// ---------------------------------------------------------------------
	'character' => [
		'path' => '/character/{slug}',
		'action' => 'index',
		'params' => [],
		'tokens' => [
			'slug' => '[a-z0-9\-]+'
		]
	],
	'user_info' => [
		'path' => '/me',
		'action' => 'me',
		'controller' => 'me',
		'verb' => 'get',
	],
	// ---------------------------------------------------------------------
	// Default / Shared routes
	// ---------------------------------------------------------------------
	'anilist-redirect' => [
		'path' => '/anilist-redirect',
		'action' => 'anilistRedirect',
		'controller' => DEFAULT_CONTROLLER,
	],
	'anilist-oauth' => [
		'path' => '/anilist-oauth',
		'action' => 'anilistCallback',
		'controller' => DEFAULT_CONTROLLER,
	],
	'image_proxy' => [
		'path' => '/public/images/{type}/{file}',
		'action' => 'images',
		'controller' => DEFAULT_CONTROLLER,
		'verb' => 'get',
		'tokens' => [
			'type' => '[a-z0-9\-]+',
			'file' => '[a-z0-9\-]+\.[a-z]{3,4}'
		]
	],
	'cache_purge' => [
		'path' => '/cache_purge',
		'action' => 'clearCache',
		'controller' => DEFAULT_CONTROLLER,
		'verb' => 'get',
	],
	'settings' => [
		'path' => '/settings',
		'action' => 'settings',
		'controller' => DEFAULT_CONTROLLER,
		'verb' => 'get',
	],
	'settings-post' => [
		'path' => '/settings',
		'action' => 'settings',
		'controller' => DEFAULT_CONTROLLER,
		'verb' => 'post',
	],
	'login' => [
		'path' => '/login',
		'action' => 'login',
		'controller' => DEFAULT_CONTROLLER,
		'verb' => 'get',
	],
	'login.post' => [
		'path' => '/login',
		'action' => 'loginAction',
		'controller' => DEFAULT_CONTROLLER,
		'verb' => 'post',
	],
	'logout' => [
		'path' => '/logout',
		'action' => 'logout',
		'controller' => DEFAULT_CONTROLLER,
	],
	'increment' => [
		'path' => '/{controller}/increment',
		'action' => 'increment',
		'verb' => 'post',
		'tokens' => [
			'controller' => '[a-z_]+',
		],
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
		'action' => 'formUpdate',
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
		'action' => DEFAULT_CONTROLLER_METHOD,
		'tokens' => [
			'type' => '[a-z_]+',
			'view' => '[a-z_]+',
		],
	],
	'index_redirect' => [
		'path' => '/',
		'controller' => DEFAULT_CONTROLLER,
		'action' => 'redirectToDefaultRoute',
	],
];