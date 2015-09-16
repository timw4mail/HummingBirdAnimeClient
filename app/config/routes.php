<?php

return [
	'convention' => [
		'default_namespace' => '\\Aviat\\AnimeClient\\Controller',
		'default_controller' => '\\Aviat\\AnimeClient\\Controller\\Anime',
		'default_method' => 'index'
	],
	'configuration' => [

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
				'code' => '301'
			]
		],
		'search' => [
			'path' => '/anime/search',
			'action' => ['search'],
		],
		'all' => [
			'path' => '/anime/all{/view}',
			'action' => ['anime_list'],
			'params' => [
				'type' => 'all',
			],
			'tokens' => [
				'view' => '[a-z_]+'
			]
		],
		'watching' => [
			'path' => '/anime/watching{/view}',
			'action' => ['anime_list'],
			'params' => [
				'type' => 'watching',
			],
			'tokens' => [
				'view' => '[a-z_]+'
			]
		],
		'plan_to_watch' => [
			'path' => '/anime/plan_to_watch{/view}',
			'action' => ['anime_list'],
			'params' => [
				'type' => 'plan_to_watch',
			],
			'tokens' => [
				'view' => '[a-z_]+'
			]
		],
		'on_hold' => [
			'path' => '/anime/on_hold{/view}',
			'action' => ['anime_list'],
			'params' => [
				'type' => 'on_hold',
			],
			'tokens' => [
				'view' => '[a-z_]+'
			]
		],
		'dropped' => [
			'path' => '/anime/dropped{/view}',
			'action' => ['anime_list'],
			'params' => [
				'type' => 'dropped',
			],
			'tokens' => [
				'view' => '[a-z_]+'
			]
		],
		'completed' => [
			'path' => '/anime/completed{/view}',
			'action' => ['anime_list'],
			'params' => [
				'type' => 'completed',
			],
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
			'path' => '/manga/all{/view}',
			'action' => ['manga_list'],
			'params' => [
				'type' => 'all',
			],
			'tokens' => [
				'view' => '[a-z_]+'
			]
		],
		'reading' => [
			'path' => '/manga/reading{/view}',
			'action' => ['manga_list'],
			'params' => [
				'type' => 'reading',
			],
			'tokens' => [
				'view' => '[a-z_]+'
			]
		],
		'plan_to_read' => [
			'path' => '/manga/plan_to_read{/view}',
			'action' => ['manga_list'],
			'params' => [
				'type' => 'plan_to_read',
			],
			'tokens' => [
				'view' => '[a-z_]+'
			]
		],
		'on_hold' => [
			'path' => '/manga/on_hold{/view}',
			'action' => ['manga_list'],
			'params' => [
				'type' => 'on_hold',
			],
			'tokens' => [
				'view' => '[a-z_]+'
			]
		],
		'dropped' => [
			'path' => '/manga/dropped{/view}',
			'action' => ['manga_list'],
			'params' => [
				'type' => 'dropped',
			],
			'tokens' => [
				'view' => '[a-z_]+'
			]
		],
		'completed' => [
			'path' => '/manga/completed{/view}',
			'action' => ['manga_list'],
			'params' => [
				'type' => 'completed',
			],
			'tokens' => [
				'view' => '[a-z_]+'
			]
		]
	]
];