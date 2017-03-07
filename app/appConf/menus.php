<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2017  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
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