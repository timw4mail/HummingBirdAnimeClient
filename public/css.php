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

// --------------------------------------------------------------------------

//Get config files
require('../app/config/minify_config.php');

//Include the css groups
$groups = require("../app/config/minify_css_groups.php");

//Function for compressing the CSS as tightly as possible
/**
 * @param string $buffer
 */
function compress($buffer) {

    //Remove CSS comments
    $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);

    //Remove tabs, spaces, newlines, etc.
    $buffer = preg_replace('`\s+`', ' ', $buffer);
    $replace = array(
    	' )' => ')',
    	') ' => ')',
    	' }' => '}',
    	'} ' => '}',
    	' {' => '{',
    	'{ ' => '{',
    	', ' => ',',
    	': ' => ':',
    	'; ' => ';',
    );

    //Eradicate every last space!
    $buffer = trim(strtr($buffer, $replace));
    $buffer = str_replace('{ ', '{', $buffer);
    $buffer = str_replace('} ', '}', $buffer);

    return $buffer;
}

function get_last_modifed()
{
	global $groups, $css_root;

	$modified = array();

	// Get all the css files, and concatenate them together
	if(isset($groups[$_GET['g']]))
	{
		foreach($groups[$_GET['g']] as $file)
		{
			$new_file = realpath($css_root.$file);
			$modified[] = filemtime($new_file);
		}
	}

	//Add myth css file for last modified check
	$modified[] = filemtime(realpath("css/base.myth.css"));

	//Add this page for last modified check
	$modified[] = filemtime(__FILE__);

	//Get the latest modified date
	rsort($modified);
	$last_modified = $modified[0];

	return $last_modified;
}

function get_css()
{
	global $groups, $path_from, $path_to, $css_root;

	$css = '';

	if(isset($groups[$_GET['g']]))
	{
		foreach($groups[$_GET['g']] as $file)
		{
			$new_file = realpath($css_root.$file);
			$css .= file_get_contents($new_file);
			$modified[] = filemtime($new_file);
		}
	}

	// If not in debug mode, minify the css
	if( ! isset($_GET['debug']))
	{
		$css = compress($css);
	}

	// Correct paths that have changed due to concatenation
	// based on rules in the config file
	$css = strtr($css, $path_from, $path_to);

	return $css;
}

// --------------------------------------------------------------------------
$last_modified = get_last_modifed();

$requested_time=(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
	? strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])
	: 0;

// Send 304 when not modified for faster response
if($last_modified === $requested_time)
{
	header("HTTP/1.1 304 Not Modified");
	exit();
}

//This GZIPs the CSS for transmission to the user
//making file size smaller and transfer rate quicker
ob_start("ob_gzhandler");

header("Content-Type: text/css; charset=utf8");
header("Cache-control: public, max-age=691200, must-revalidate");
header("Last-Modified: ".gmdate('D, d M Y H:i:s', $last_modified)." GMT");
header("Expires: ".gmdate('D, d M Y H:i:s', (filemtime(basename(__FILE__)) + 691200))." GMT");

echo get_css();

ob_end_flush();
//End of css.php