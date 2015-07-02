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

//Get config files
require('../app/config/minify_config.php');

//Include the js groups
$groups_file = "../app/config/minify_js_groups.php";
$groups = require($groups_file);

//The name of this file
$this_file = __FILE__;

// --------------------------------------------------------------------------

/**
 * Get Files
 *
 * Concatenates the javascript files for the current
 * group as a string
 * @return string
 */
function get_files()
{
	global $groups, $js_root;

	$js = '';

	foreach($groups[$_GET['g']] as $file)
	{
		$new_file = realpath($js_root.$file);
		$js .= file_get_contents($new_file) . "\n\n";
	}

	return $js;
}

// --------------------------------------------------------------------------

/**
 * Google Min
 *
 * Minifies javascript using google's closure compiler
 * @param string $new_file
 * @return string
 */
function google_min($new_file)
{
	$options = [
		'output_info' => 'compiled_code',
		'output_format' => 'json',
		'compilation_level' => 'SIMPLE_OPTIMIZATIONS',
		'js_code' => urlencode($new_file),
		'warning_level' => 'QUIET',
		'language' => 'ECMASCRIPT5'
	];

	$option_pairs = [];
	foreach($options as $key => $value)
	{
		$option_pairs[] = "{$key}={$value}";
	}
	$option_string = implode("&", $option_pairs);

	//Get a much-minified version from Google's closure compiler
	$ch = curl_init('http://closure-compiler.appspot.com/compile');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $option_string);
	$json = curl_exec($ch);
	curl_close($ch);

	$obj = json_decode($json);
	return $obj->compiledCode;
}

// --------------------------------------------------------------------------

//Creative rewriting of /g/groupname to ?g=groupname
$pi = $_SERVER['PATH_INFO'];
$pia = explode('/', $pi);

$pia_len = count($pia);
$i = 1;

while($i < $pia_len)
{
	$j = $i+1;
	$j = (isset($pia[$j])) ? $j : $i;

	$_GET[$pia[$i]] = $pia[$j];

	$i = $j + 1;
};

// --------------------------------------------------------------------------

$js = '';
$modified = array();

// --------------------------------------------------------------------------

//Aggregate the last modified times of the files
if(isset($groups[$_GET['g']]))
{
	if ( ! is_dir($js_root . 'cache'))
	{
		mkdir($js_root . 'cache');
	}
	$cache_file = $js_root.'cache/'.$_GET['g'];

	foreach($groups[$_GET['g']] as $file)
	{
		$new_file = realpath($js_root.$file);
		$modified[] = filemtime($new_file);
	}

	//Add this page too, as well as the groups file
	$modified[] = filemtime($this_file);
	$modified[] = filemtime($groups_file);

	$cache_modified = 0;

	//Add the cache file
	if(is_file($cache_file))
	{
		$cache_modified = filemtime($cache_file);
	}
}
else //Nothing to display? Just exit
{
	die("You must specify a group that exists");
}

// --------------------------------------------------------------------------

//Get the latest modified date
rsort($modified);
$last_modified = $modified[0];

$requested_time=(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
	? strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])
	: time();

// If the browser's cached version is up to date,
// don't resend the file
if($last_modified === $requested_time)
{
	header("HTTP/1.1 304 Not Modified");
	exit();
}

// --------------------------------------------------------------------------

//Determine what to do: rebuild cache, send files as is, or send cache.
// If debug is set, just concatenate
if(isset($_GET['debug']))
{
	$js = get_files();
}
else if($cache_modified < $last_modified)
{
	$js = google_min(get_files());

	//Make sure cache file gets created/updated
	if(file_put_contents($cache_file, $js) === FALSE)
	{
		die("Cache file was not created. Make sure you have the correct folder permissions.");
	}
}
// Otherwise, send the cached file
else
{
	$js = file_get_contents($cache_file);
}

// --------------------------------------------------------------------------

//This GZIPs the js for transmission to the user
//making file size smaller and transfer rate quicker
ob_start("ob_gzhandler");

// Set important caching headers
header("Content-Type: application/javascript; charset=utf8");
header("Cache-control: public, max-age=691200, must-revalidate");
header("Last-Modified: ".gmdate('D, d M Y H:i:s', $last_modified)." GMT");
header("Expires: ".gmdate('D, d M Y H:i:s', (filemtime($this_file) + 691200))." GMT");

echo $js;

ob_end_flush();
//end of js.php