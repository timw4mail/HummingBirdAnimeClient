<?php
/**
 * Hummingbird Anime Client
 *
 * An API client for Hummingbird to manage anime and manga watch lists
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren
 * @copyright   Copyright (c) 2015
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 * @license     MIT
 */

return [
	'anime_list' => [
		'route_prefix' => '/anime',
		'items' => [
			'watching' => '/watching',
			'plan_to_watch' => '/plan_to_watch',
			'on_hold' => '/on_hold',
			'dropped' => '/dropped',
			'completed' => '/completed',
			//'collection' => '/collection/view',
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