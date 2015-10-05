<?php

return [
	'top' => [
		'default' => '',
		'items' => [
			'anime_list' => '{anime_list}',
			'manga_list' => '{manga_list}',
			'collection' => '{collection}'
		]
	],
	'view_type' => [
		'is_parent' => FALSE,
		'default' => 'cover_view',
		'items' => [
			'cover_view' =>  '{parent}',
			'list_view' => '{parent}/list'
		]
	],
	'anime_list' => [
		'default' => '',
		'route_prefix' => '/anime',
		'items' => [
			'watching' => '/watching',
			'plan_to_watch' => '/plan_to_watch',
			'on_hold' => '/on_hold',
			'dropped' => '/dropped',
			'completed' => '/completed',
			'all' => '/all'
		],
		'children' => [
			'view_type'
		]
	],
	'manga_list' => [
		'default' => '',
		'route_prefix' => '/manga',
		'items' => [
			'reading' => '/reading',
			'plan_to_read' => '/plan_to_read',
			'on_hold' => '/on_hold',
			'dropped' => '/dropped',
			'completed' => '/completed',
			'all' => '/all'
		],
		'children' => [
			'view_type'
		]
	],
	'collection' => [
		'default' => '',
		'route_prefix' => '/collection',
		'items' => [
			'anime' => '/anime',
			'manga' => '/manga',
		],
		'children' => [
			'view_type'
		]
	]
];