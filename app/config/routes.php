<?php

return [
	'anime' => [
		'index' => [
			'path' => '/',
			'controller' => 'AnimeController'
		],
		'all' => [
			'path' => '/all',
			'controller' => 'AnimeController',
			'action' => 'all'
		],
		'plan_to_watch' => [
			'path' => '/plan_to_watch',
			'controller' => 'AnimeController',
			'action' => 'anime_list',
			'params' => [
				'type' => 'plan-to-watch'
			]
		],
		'on_hold' => [
			'path' => '/on_hold',
			'controller' => 'AnimeController',
			'action' => 'anime_list',
			'params' => [
				'type' => 'on-hold'
			]
		],
		'dropped' => [
			'path' => '/dropped',
			'controller' => 'AnimeController',
			'action' => 'anime_list',
			'params' => [
				'type' => 'dropped'
			]
		],
		'completed' => [
			'path' => '/completed',
			'controller' => 'AnimeController',
			'action' => 'anime_list',
			'params' => [
				'type' => 'completed'
			]
		],
		'anime_login' => [
			'path' => '/login',
			'controller' => 'AnimeController',
			'action' => 'login'
		]
	],
	'manga' => [
		'index' => [
			'path' => '/',
			'controller' => 'MangaController'
		],
		'all' => [
			'path' => '/all',
			'controller' => 'MangaController',
			'action' => 'all'
		],
		'plan_to_read' => [
			'path' => '/plan_to_read',
			'controller' => 'MangaController',
			'action' => 'manga_list',
			'params' => [
				'type' => 'Plan to Read'
			]
		],
		'on_hold' => [
			'path' => '/on_hold',
			'controller' => 'MangaController',
			'action' => 'manga_list',
			'params' => [
				'type' => 'On Hold'
			]
		],
		'dropped' => [
			'path' => '/dropped',
			'controller' => 'MangaController',
			'action' => 'manga_list',
			'params' => [
				'type' => 'Dropped'
			]
		],
		'completed' => [
			'path' => '/completed',
			'controller' => 'MangaController',
			'action' => 'manga_list',
			'params' => [
				'type' => 'Completed'
			]
		],
	]
];