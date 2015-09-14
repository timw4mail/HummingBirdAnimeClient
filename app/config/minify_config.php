<?php
/**
 * Easy Min
 *
 * Simple minification for better website performance
 *
 * @author		Timothy J. Warren
 * @copyright	Copyright (c) 2012
 * @link 		https://github.com/aviat4ion/Easy-Min
 * @license		http://philsturgeon.co.uk/code/dbad-license
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