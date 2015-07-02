<?php

return [
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
	// Routes on anime controller
	'anime' => [
		'index' => [
			'path' => '/',
			'action' => ['redirect'],
			'params' => [
				'url' => '', // Determined by config
				'code' => '301'
			]
		],
		'search' => [
			'path' => '/search',
			'action' => ['search'],
		],
		'all' => [
			'path' => '/all{/view}',
			'action' => ['anime_list'],
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
			'action' => ['anime_list'],
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
			'action' => ['anime_list'],
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
			'action' => ['anime_list'],
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
			'action' => ['anime_list'],
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
			'action' => ['anime_list'],
			'params' => [
				'type' => 'completed',
				'title' => WHOSE . " Anime List &middot; Completed"
			],
			'tokens' => [
				'view' => '[a-z_]+'
			]
		],
		'collection_add_form' => [
			'path' => '/collection/add',
			'action' => ['collection_form'],
			'params' => [],
		],
		'collection_edit_form' => [
			'path' => '/collection/edit/{id}',
			'action' => ['collection_form'],
			'tokens' => [
				'id' => '[0-9]+'
			]
		],
		'collection_add' => [
			'path' => '/collection/add',
			'action' => ['collection_add'],
			'verb' => 'post'
		],
		'collection_edit' => [
			'path' => '/collection/edit',
			'action' => ['collection_edit'],
			'verb' => 'post'
		],
		'collection' => [
			'path' => '/collection/view{/view}',
			'action' => ['collection'],
			'params' => [],
			'tokens' => [
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
		'all' => [
			'path' => '/all{/view}',
			'action' => ['manga_list'],
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
			'action' => ['manga_list'],
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
			'action' => ['manga_list'],
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
			'action' => ['manga_list'],
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
			'action' => ['manga_list'],
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
			'action' => ['manga_list'],
			'params' => [
				'type' => 'Completed',
				'title' => WHOSE . " Manga List &middot; Completed"
			],
			'tokens' => [
				'view' => '[a-z_]+'
			]
		]
	]
];