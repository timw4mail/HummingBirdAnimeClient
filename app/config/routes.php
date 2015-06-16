<?php

return [
	'anime' => [
		'index' => [
			'path' => '/',
			'controller' => 'AnimeController',
			'action' => 'redirect',
			'params' => [
				'url' => '', // Determined by config
				'code' => '301'
			]
		],
		'all' => [
			'path' => '/all{/view}',
			'controller' => 'AnimeController',
			'action' => 'anime_list',
			'params' => [
				'type' => 'all',
				'title' => WHOSE . " Anime List &middot; All"
			],
			'tokens' => [
				'view' => '[a-z_]+'
			]
		],
		'watching' => [
			'path' => '/watching{/view}',
			'controller' => 'AnimeController',
			'action' => 'anime_list',
			'params' => [
				'type' => 'currently-watching',
				'title' => WHOSE . " Anime List &middot; Watching"
			],
			'tokens' => [
				'view' => '[a-z_]+'
			]
		],
		'plan_to_watch' => [
			'path' => '/plan_to_watch{/view}',
			'controller' => 'AnimeController',
			'action' => 'anime_list',
			'params' => [
				'type' => 'plan-to-watch',
				'title' => WHOSE . " Anime List &middot; Plan to Watch"
			],
			'tokens' => [
				'view' => '[a-z_]+'
			]
		],
		'on_hold' => [
			'path' => '/on_hold{/view}',
			'controller' => 'AnimeController',
			'action' => 'anime_list',
			'params' => [
				'type' => 'on-hold',
				'title' => WHOSE . " Anime List &middot; On Hold"
			],
			'tokens' => [
				'view' => '[a-z_]+'
			]
		],
		'dropped' => [
			'path' => '/dropped{/view}',
			'controller' => 'AnimeController',
			'action' => 'anime_list',
			'params' => [
				'type' => 'dropped',
				'title' => WHOSE . " Anime List &middot; Dropped"
			],
			'tokens' => [
				'view' => '[a-z_]+'
			]
		],
		'completed' => [
			'path' => '/completed{/view}',
			'controller' => 'AnimeController',
			'action' => 'anime_list',
			'params' => [
				'type' => 'completed',
				'title' => WHOSE . " Anime List &middot; Completed"
			],
			'tokens' => [
				'view' => '[a-z_]+'
			]
		],
		'collection' => [
			'path' => '/collection{/view}',
			'controller' => 'AnimeController',
			'action' => 'collection',
			'params' => [],
			'tokens' => [
				'view' => '[a-z_]+'
			]
		]
	],
	'manga' => [
		'index' => [
			'path' => '/',
			'controller' => 'MangaController',
			'action' => 'redirect',
			'params' => [
				'url' => '', // Determined by config
				'code' => '301',
				'type' => 'manga'
			]
		],
		'all' => [
			'path' => '/all{/view}',
			'controller' => 'MangaController',
			'action' => 'manga_list',
			'params' => [
				'type' => 'all',
				'title' => WHOSE . " Manga List &middot; All"
			],
			'tokens' => [
				'view' => '[a-z_]+'
			]
		],
		'reading' => [
			'path' => '/reading{/view}',
			'controller' => 'MangaController',
			'action' => 'manga_list',
			'params' => [
				'type' => 'Reading',
				'title' => WHOSE . " Manga List &middot; Reading"
			],
			'tokens' => [
				'view' => '[a-z_]+'
			]
		],
		'plan_to_read' => [
			'path' => '/plan_to_read{/view}',
			'controller' => 'MangaController',
			'action' => 'manga_list',
			'params' => [
				'type' => 'Plan to Read',
				'title' => WHOSE . " Manga List &middot; Plan to Read"
			],
			'tokens' => [
				'view' => '[a-z_]+'
			]
		],
		'on_hold' => [
			'path' => '/on_hold{/view}',
			'controller' => 'MangaController',
			'action' => 'manga_list',
			'params' => [
				'type' => 'On Hold',
				'title' => WHOSE . " Manga List &middot; On Hold"
			],
			'tokens' => [
				'view' => '[a-z_]+'
			]
		],
		'dropped' => [
			'path' => '/dropped{/view}',
			'controller' => 'MangaController',
			'action' => 'manga_list',
			'params' => [
				'type' => 'Dropped',
				'title' => WHOSE . " Manga List &middot; Dropped"
			],
			'tokens' => [
				'view' => '[a-z_]+'
			]
		],
		'completed' => [
			'path' => '/completed{/view}',
			'controller' => 'MangaController',
			'action' => 'manga_list',
			'params' => [
				'type' => 'Completed',
				'title' => WHOSE . " Manga List &middot; Completed"
			],
			'tokens' => [
				'view' => '[a-z_]+'
			]
		],
	]
];