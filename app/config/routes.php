<?php

return [
	'anime' => [
		'all' => [
			'path' => '/all',
			'controller' => 'AnimeController',
			'action' => 'anime_list',
			'params' => [
				'type' => 'all',
				'title' => WHOSE . " Anime List &middot All"
			]
		],
		'index' => [
			'path' => '/',
			'controller' => 'AnimeController',
			'action' => 'anime_list',
			'params' => [
				'type' => 'currently-watching',
				'title' => WHOSE . " Anime List &middot Watching"
			]
		],
		'plan_to_watch' => [
			'path' => '/plan_to_watch',
			'controller' => 'AnimeController',
			'action' => 'anime_list',
			'params' => [
				'type' => 'plan-to-watch',
				'title' => WHOSE . " Anime List &middot Plan to Watch"
			]
		],
		'on_hold' => [
			'path' => '/on_hold',
			'controller' => 'AnimeController',
			'action' => 'anime_list',
			'params' => [
				'type' => 'on-hold',
				'title' => WHOSE . " Anime List &middot On Hold"
			]
		],
		'dropped' => [
			'path' => '/dropped',
			'controller' => 'AnimeController',
			'action' => 'anime_list',
			'params' => [
				'type' => 'dropped',
				'title' => WHOSE . " Anime List &middot Dropped"
			]
		],
		'completed' => [
			'path' => '/completed',
			'controller' => 'AnimeController',
			'action' => 'anime_list',
			'params' => [
				'type' => 'completed',
				'title' => WHOSE . " Anime List &middot Completed"
			]
		],
		'collection' => [
			'path' => '/collection',
			'controller' => 'AnimeController',
			'action' => 'collection',
			'params' => []
		]
	],
	'manga' => [
		'all' => [
			'path' => '/all',
			'controller' => 'MangaController',
			'action' => 'manga_list',
			'params' => [
				'type' => 'all',
				'title' => WHOSE . " Manga List &middot; All"
			]
		],
		'index' => [
			'path' => '/',
			'controller' => 'MangaController',
			'action' => 'manga_list',
			'params' => [
				'type' => 'Reading',
				'title' => WHOSE . " Manga List &middot; Reading"
			]
		],
		'plan_to_read' => [
			'path' => '/plan_to_read',
			'controller' => 'MangaController',
			'action' => 'manga_list',
			'params' => [
				'type' => 'Plan to Read',
				'title' => WHOSE . " Manga List &middot; Plan to Read"
			]
		],
		'on_hold' => [
			'path' => '/on_hold',
			'controller' => 'MangaController',
			'action' => 'manga_list',
			'params' => [
				'type' => 'On Hold',
				'title' => WHOSE . " Manga List &middot; On Hold"
			]
		],
		'dropped' => [
			'path' => '/dropped',
			'controller' => 'MangaController',
			'action' => 'manga_list',
			'params' => [
				'type' => 'Dropped',
				'title' => WHOSE . " Manga List &middot; Dropped"
			]
		],
		'completed' => [
			'path' => '/completed',
			'controller' => 'MangaController',
			'action' => 'manga_list',
			'params' => [
				'type' => 'Completed',
				'title' => WHOSE . " Manga List &middot; Completed"
			]
		],
	]
];