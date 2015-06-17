<?php

return [
	'anime' => [
		'index' => [
			'path' => '/',
			'action' => ['AnimeController', 'redirect'],
			'params' => [
				'url' => '', // Determined by config
				'code' => '301'
			]
		],
		'all' => [
			'path' => '/all{/view}',
			'action' => ['AnimeController', 'anime_list'],
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
			'action' => ['AnimeController', 'anime_list'],
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
			'action' => ['AnimeController', 'anime_list'],
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
			'action' => ['AnimeController', 'anime_list'],
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
			'action' => ['AnimeController', 'anime_list'],
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
			'action' => ['AnimeController', 'anime_list'],
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
			'action' => ['AnimeController', 'collection'],
			'params' => [],
			'tokens' => [
				'view' => '[a-z_]+'
			]
		]
	],
	'manga' => [
		'index' => [
			'path' => '/',
			'action' => ['MangaController', 'redirect'],
			'params' => [
				'url' => '', // Determined by config
				'code' => '301',
				'type' => 'manga'
			]
		],
		'all' => [
			'path' => '/all{/view}',
			'action' => ['MangaController', 'manga_list'],
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
			'action' => ['MangaController', 'manga_list'],
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
			'action' => ['MangaController', 'manga_list'],
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
			'action' => ['MangaController', 'manga_list'],
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
			'action' => ['MangaController', 'manga_list'],
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
			'action' => ['MangaController', 'manga_list'],
			'params' => [
				'type' => 'Completed',
				'title' => WHOSE . " Manga List &middot; Completed"
			],
			'tokens' => [
				'view' => '[a-z_]+'
			]
		],
	],
	// These routes are limited to a specific HTTP verb
	'get' => [
		'login_form' => [
			'path' => '/login',
			'action' => ['AnimeController', 'login'],
		],
		'logout' => [
			'path' => '/logout',
			'action' => ['BaseController', 'logout']
		]
	],
	'post' => [
		'login_action' => [
			'path' => '/login',
			'action' => ['AnimeController', 'login_action'],
		]
	]
];