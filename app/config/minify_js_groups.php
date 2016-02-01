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

// --------------------------------------------------------------------------

$bower_packages = ['jquery', 'datatables', 'mustache.js'];
$bower_file_map = [];

foreach($bower_packages as $package)
{
	$bower_file_map[$package] = [];
	$json = json_decode(file_get_contents(__DIR__ . "/../../public/bower_components/{$package}/bower.json"));

	if ( ! is_array($json->main))
	{
		$json->main = [$json->main];
	}

	foreach($json->main as $file)
	{
		if (stristr($file, '.js') !== FALSE)
		{
			array_push($bower_file_map[$package], "bower_components/{$package}/{$file}");
		}
	}
}

/**
 * Merge together bower configs and local files
 *
 * @param  string|array  $bower - array of bower components to include
 * @param  string|array $local - array of local js files to include
 * @return array - group array
 */
function create_group($bower, $local=[])
{
	global $bower_file_map;
	$group = [];

	foreach((array) $bower as $component)
	{
		$group = array_merge($group, $bower_file_map[$component]);
	}

	foreach((array) $local as $file)
	{
		$group[] = $file;
	}

	return $group;
}

/**
 * This is the config array for javascript files to concatenate and minify
 */
$map = [
	/*
		For each group create an array like so

		'my_group' => array(
			'path/to/js/file1.js',
			'path/to/js/file2.js'
		),
	*/
	'base' => create_group('jquery', [
		'js/base.js',
	]),
	'event' => create_group([], 'js/events.js'),
	'table' => create_group([], 'js/sort_tables.js'),
	'table_edit' => create_group([], [
		'js/sort_tables.js',
		'js/anime_edit.js',
		'js/manga_edit.js',
	]),
	'edit' => create_group([],[
		'js/anime_edit.js',
		'js/manga_edit.js'
	]),
	'anime_collection' => create_group('mustache.js', [
		'bower_components/jquery-throttle-debounce/jquery.ba-throttle-debounce.js',
		'js/anime_collection.js'
	]),
	'manga_collection' => create_group('mustache.js', [
		'bower_components/jquery-throttle-debounce/jquery.ba-throttle-debounce.js',
		'js/manga_collection.js'
	]),
];

//print_r($map);
//die();

return $map;

// End of js_groups.php