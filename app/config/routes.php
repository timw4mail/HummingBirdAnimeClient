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

return [
	'convention' => [
		'default_namespace' => '\\Aviat\\AnimeClient\\Controller',
		'default_controller' => '\\Aviat\\AnimeClient\\Controller\\Anime',
		'default_method' => 'index',
		'404_method' => 'not_found'
	],
	// Routes on anime collection controller
	'anime' => [
		'anime_add_form' => [
			'path' => '/anime/add',
			'action' => 'add_form',
			'verb' => 'get'
		],
		'anime_add' => [
			'path' => '/anime/add',
			'action' => 'add',
			'verb' => 'post'
		]
	],
	'manga' => [

	],
	'collection' => [
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
	],
];
