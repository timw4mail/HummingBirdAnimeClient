<?php

return [
	'convention' => [
		'default_namespace' => '\\Aviat\\AnimeClient\\Controller',
		'default_controller' => '\\Aviat\\AnimeClient\\Controller\\Anime',
		'default_method' => 'index'
	],
	// Routes on all controllers
	'common' => [
		'update' => [
			'path' => '/update',
			'action' => ['update'],
			'verb' => 'post'
		],
		'login_form' => [
			'path' => '/login',
			'action' => ['login'],
			'verb' => 'get'
		],
		'login_action' => [
			'path' => '/login',
			'action' => ['login_action'],
			'verb' => 'post'
		],
		'logout' => [
			'path' => '/logout',
			'action' => ['logout']
		],
	],
	// Routes on collection controller
	'collection' => [
		'collection_add_form' => [
			'path' => '/collection/add',
			'action' => ['form'],
			'params' => [],
		],
		'collection_edit_form' => [
			'path' => '/collection/edit/{id}',
			'action' => ['form'],
			'tokens' => [
				'id' => '[0-9]+'
			]
		],
		'collection_add' => [
			'path' => '/collection/add',
			'action' => ['add'],
			'verb' => 'post'
		],
		'collection_edit' => [
			'path' => '/collection/edit',
			'action' => ['edit'],
			'verb' => 'post'
		],
		'collection' => [
			'path' => '/collection/view{/view}',
			'action' => ['index'],
			'params' => [],
			'tokens' => [
				'view' => '[a-z_]+'
			]
		],
	],
	// Routes on anime controller
	'anime' => [
		'index' => [
			'path' => '/',
			'action' => ['redirect'],
			'params' => [
				'url' => '', // Determined by config
				'code' => '301',
				'type' => 'anime'
			]
		],
		'search' => [
			'path' => '/anime/search',
			'action' => ['search'],
		],
		'anime_list' => [
			'path' => '/anime/{type}{/view}',
			'action' => ['anime_list'],
			'tokens' => [
				'type' => '[a-z_]+',
				'view' => '[a-z_]+'
			]
		],
	],
	'manga' => [
		'index' => [
			'path' => '/',
			'action' => ['redirect'],
			'params' => [
				'url' => '', // Determined by config
				'code' => '301',
				'type' => 'manga'
			]
		],
		'manga_list' => [
			'path' => '/manga/{type}{/view}',
			'action' => ['manga_list'],
			'tokens' => [
				'type' => '[a-z_]+',
				'view' => '[a-z_]+'
			]
		]
	]
];
