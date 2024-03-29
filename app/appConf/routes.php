<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2021  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

use const Aviat\AnimeClient\{
	ALPHA_SLUG_PATTERN,
	DEFAULT_CONTROLLER,
	DEFAULT_CONTROLLER_METHOD,
	SLUG_PATTERN,
	NUM_PATTERN,
};

// -------------------------------------------------------------------------
// Routing Config
//
// Maps paths to controllers and methods
// -------------------------------------------------------------------------
$base_routes = [
	// ---------------------------------------------------------------------
	// AJAX Routes
	// ---------------------------------------------------------------------
	'cache_purge' => [
		'path' => '/cache_purge',
		'action' => 'clearCache',
	],
	'heartbeat' => [
		'path' => '/heartbeat',
		'action' => 'heartbeat',
	],
	// ---------------------------------------------------------------------
	// Anime List Routes
	// ---------------------------------------------------------------------
	'anime.add.get' => [
		'path' => '/anime/add',
		'action' => 'addForm',
	],
	'anime.add.post' => [
		'path' => '/anime/add',
		'action' => 'add',
		'verb' => 'post',
	],
	'anime.random' => [
		'path' => '/anime/details/random',
		'action' => 'random',
	],
	'anime.details' => [
		'path' => '/anime/details/{id}',
		'action' => 'details',
		'tokens' => [
			'id' => SLUG_PATTERN,
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
	'manga.random' => [
		'path' => '/manga/details/random',
		'action' => 'random',
	],
	'manga.details' => [
		'path' => '/manga/details/{id}',
		'action' => 'details',
		'tokens' => [
			'id' => SLUG_PATTERN,
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
	],
	'anime.collection.edit.get' => [
		'path' => '/anime-collection/edit/{id}',
		'action' => 'form',
		'tokens' => [
			'id' => NUM_PATTERN,
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
		'action' => 'view',
		'tokens' => [
			'view' => ALPHA_SLUG_PATTERN,
		],
	],
	'anime.collection.delete' => [
		'path' => '/anime-collection/delete',
		'action' => 'delete',
		'verb' => 'post',
	],
	'anime.collection.redirect' => [
		'path' => '/anime-collection',
	],
	'anime.collection.redirect2' => [
		'path' => '/anime-collection/',
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
	],
	'manga.collection.edit.get' => [
		'path' => '/manga-collection/edit/{id}',
		'action' => 'form',
		'tokens' => [
			'id' => NUM_PATTERN,
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
		'tokens' => [
			'view' => ALPHA_SLUG_PATTERN,
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
		'tokens' => [
			'slug' => SLUG_PATTERN,
		],
	],
	'person' => [
		'path' => '/people/{slug}',
		'tokens' => [
			'slug' => SLUG_PATTERN,
		],
	],
	'default_user_info' => [
		'path' => '/me',
		'action' => 'me',
		'controller' => 'user',
	],
	'user_info' => [
		'path' => '/user/{username}',
		'controller' => 'user',
		'action' => 'about',
		'tokens' => [
			'username' => '.*?',
		],
	],
	// ---------------------------------------------------------------------
	// Default / Shared routes
	// ---------------------------------------------------------------------
	'anilist-redirect' => [
		'path' => '/anilist-redirect',
		'action' => 'anilistRedirect',
		'controller' => 'settings',
	],
	'anilist-callback' => [
		'path' => '/anilist-oauth',
		'action' => 'anilistCallback',
		'controller' => 'settings',
	],
	'image_proxy' => [
		'path' => '/public/images/{type}/{file}',
		'action' => 'cache',
		'controller' => 'images',
		'tokens' => [
			'type' => SLUG_PATTERN,
			'file' => '[a-z0-9\-]+\.[a-z]{3,4}',
		],
	],
	'settings' => [
		'path' => '/settings',
	],
	'settings-post' => [
		'path' => '/settings/update',
		'action' => 'update',
		'verb' => 'post',
	],
	'login' => [
		'path' => '/login',
		'action' => 'login',
	],
	'login.post' => [
		'path' => '/login',
		'action' => 'loginAction',
		'verb' => 'post',
	],
	'logout' => [
		'path' => '/logout',
		'action' => 'logout',
	],
	'history' => [
		'controller' => 'history',
		'path' => '/history/{type}',
		'tokens' => [
			'type' => SLUG_PATTERN,
		],
	],
	'increment' => [
		'path' => '/{controller}/increment',
		'action' => 'increment',
		'verb' => 'post',
		'tokens' => [
			'controller' => ALPHA_SLUG_PATTERN,
		],
	],
	'update' => [
		'path' => '/{controller}/update',
		'action' => 'update',
		'verb' => 'post',
		'tokens' => [
			'controller' => ALPHA_SLUG_PATTERN,
		],
	],
	'update.post' => [
		'path' => '/{controller}/update_form',
		'action' => 'formUpdate',
		'verb' => 'post',
		'tokens' => [
			'controller' => ALPHA_SLUG_PATTERN,
		],
	],
	'edit' => [
		'path' => '/{controller}/edit/{id}/{status}',
		'action' => 'edit',
		'tokens' => [
			'id' => SLUG_PATTERN,
			'status' => SLUG_PATTERN,
		],
	],
	'list' => [
		'path' => '/{controller}/{status}{/view}',
		'tokens' => [
			'status' => ALPHA_SLUG_PATTERN,
			'view' => ALPHA_SLUG_PATTERN,
		],
	],
	'index_redirect' => [
		'path' => '/',
		'action' => 'redirectToDefaultRoute',
	],
];

$defaultMap = [
	'action' => DEFAULT_CONTROLLER_METHOD,
	'controller' => DEFAULT_CONTROLLER,
	'params' => [],
	'verb' => 'get',
];

$routes = [];
foreach ($base_routes as $name => $route)
{
	$routes[$name] = array_merge($defaultMap, $route);
}

return $routes;
