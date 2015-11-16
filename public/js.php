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
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

//Get config files
require_once('../app/config/minify_config.php');

//Include the js groups
$groups_file = "../app/config/minify_js_groups.php";
$groups = require_once($groups_file);

// Include guzzle
require_once('../vendor/autoload.php');

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
		'output_info' => 'errors',
		'output_format' => 'json',
		'compilation_level' => 'SIMPLE_OPTIMIZATIONS',
		'js_code' => $new_file,
		'language' => 'ECMASCRIPT5',
		'language_out' => 'ECMASCRIPT5_STRICT'
	];

	// First check for errors
	$error_client = new Client();
	$error_res = $error_client->post('http://closure-compiler.appspot.com/compile', [
		'headers' => [
			'Accept-Encoding' => 'gzip',
			"Content-type" => "application/x-www-form-urlencoded"
		],
		'form_params' => $options
	]);

	$error_json = $error_res->getBody();
	$error_obj = json_decode($error_json);

	if ( ! empty($error_obj->errors))
	{
		?><pre><?= json_encode($err_obj, JSON_PRETTY_PRINT) ?></pre><?php
		die();
	}

	// Now actually retrieve the compiled code
	$options['output_info'] = 'compiled_code';
	$client = new Client();
	$res = $client->post('http://closure-compiler.appspot.com/compile', [
		'headers' => [
			'Accept-Encoding' => 'gzip',
			"Content-type" => "application/x-www-form-urlencoded"
		],
		'form_params' => $options
	]);

	$json = $res->getBody();
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