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

/* $config = */require 'config.php';

// Should we use myth to preprocess?
$use_myth = FALSE;

/*
|--------------------------------------------------------------------------
| CSS Folder
|--------------------------------------------------------------------------
|
| The folder where css files exist, in relation to the document root
|
*/
$css_root = $config['asset_dir'] . '/css/';

/*
|--------------------------------------------------------------------------
| Path from
|--------------------------------------------------------------------------
|
| Path fragment to rewrite in css files
|
*/
$path_from = '';

/*
|--------------------------------------------------------------------------
| Path to
|--------------------------------------------------------------------------
|
| The path fragment replacement for the css files
|
*/
$path_to = '';

/*
|--------------------------------------------------------------------------
| JS Folder
|--------------------------------------------------------------------------
|
| The folder where javascript files exist, in relation to the document root
|
*/
$js_root = $config['asset_dir'] . '/js/';