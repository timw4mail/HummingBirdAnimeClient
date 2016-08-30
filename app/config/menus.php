<?php
/**
 * Hummingbird Anime Client
 *
 * An API client for Hummingbird to manage anime and manga watch lists
 *
 * PHP version 5.6
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2016  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     3.1
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
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
	]
];