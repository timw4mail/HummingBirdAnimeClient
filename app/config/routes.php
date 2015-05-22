<?php

return [
	'anime_all' => [
		'path' => '/all',
		'controller' => 'AnimeController',
		'action' => 'all',
		'params' => []
	],
	'anime_plan_to_watch' => [
		'path' => '/plan_to_watch',
		'controller' => 'AnimeController',
		'action' => 'anime_list',
		'params' => [
			'type' => 'plan-to-watch'
		]
	],
	'anime_on_hold' => [
		'path' => '/on_hold',
		'controller' => 'AnimeController',
		'action' => 'anime_list',
		'params' => [
			'type' => 'on-hold'
		]
	],
	'anime_dropped' => [
		'path' => '/dropped',
		'controller' => 'AnimeController',
		'action' => 'anime_list',
		'params' => [
			'type' => 'dropped'
		]
	],
	'anime_completed' => [
		'path' => '/completed',
		'controller' => 'AnimeController',
		'action' => 'anime_list',
		'params' => [
			'type' => 'completed'
		]
	]
];