<?php

return [
	'anime_list' => [
		'route_prefix' => '/anime',
		'items' => [
			'watching' => '/watching',
			'plan_to_watch' => '/plan_to_watch',
			'on_hold' => '/on_hold',
			'dropped' => '/dropped',
			'completed' => '/completed',
			'all' => '/all'
		]
	],
	'manga_list' => [
		'route_prefix' => '/manga',
		'items' => [
			'reading' => '/reading',
			'plan_to_read' => '/plan_to_read',
			'on_hold' => '/on_hold',
			'dropped' => '/dropped',
			'completed' => '/completed',
			'all' => '/all'
		]
	],
	'collection' => [
		'route_prefix' => '/collection',
		'items' => [
			'anime' => '/anime',
			'manga' => '/manga',
		]
	]
];