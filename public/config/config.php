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

// Should we use myth to preprocess?
$use_myth = TRUE;

/*
|--------------------------------------------------------------------------
| Document Root
|--------------------------------------------------------------------------
|
| The folder where the index of the website exists. In most situations,
| this will not need to be changed.
|
| If the website is in a folder off of the domain name, like:
|	http://example.com/website/
| you will need to add that folder to the document root.
|
*/
$document_root = $_SERVER['DOCUMENT_ROOT'];

/*
|--------------------------------------------------------------------------
| CSS Folder
|--------------------------------------------------------------------------
|
| The folder where css files exist, in relation to the document root
|
*/
$css_root = $document_root. '/public/css/';

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
$js_root = $document_root. '/public/js/';