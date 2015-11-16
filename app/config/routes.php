<?php
/**
 * Hummingbird Anime Client
 *
 * An API client for Hummingbird to manage anime and manga watch lists
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren
 * @copyright   Copyright (c) 2015
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
	// Routes on all controllers
	'common' => [
		'update' => [
			'path' => '/{controller}/update',
			'action' => 'update',
			'verb' => 'post',
			'tokens' => [
				'controller' => '[a-z_]+'
			]
		],
	],
	// Routes on collection controller
	'collection' => [
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
