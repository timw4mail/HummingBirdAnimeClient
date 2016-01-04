<?php
/**
 * Hummingbird Anime Client
 *
 * An API client for Hummingbird to manage anime and manga watch lists
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren
 * @copyright   Copyright (c) 2015 - 2016
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 * @license     MIT
 */

return [
	'collection' => [
		'type' => 'sqlite',
		'host' => '',
		'user' => '',
		'pass' => '',
		'port' => '',
		'name' => 'default',
		'database'   => '',
		'file' => __DIR__ . '/../../anime_collection.sqlite',
	]
];