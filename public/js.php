<?php
/**
 * Hummingbird Anime Client
 *
 * An API client for Hummingbird to manage anime and manga watch lists
 *
 * @package	 HummingbirdAnimeClient
 * @author	  Timothy J. Warren
 * @copyright   Copyright (c) 2015 - 2016
 * @link		https://github.com/timw4mail/HummingBirdAnimeClient
 * @license	 MIT
 */

namespace Aviat\EasyMin;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

// Include guzzle
require_once('../vendor/autoload.php');

/**
 * Simple Javascript minfier, using google closure compiler
 */
class JSMin {

	protected $js_root;
	protected $js_group;
	protected $js_groups_file;
	protected $cache_file;

	protected $last_modified;
	protected $requested_time;
	protected $cache_modified;

	public function __construct(array $config, array $groups)
	{
		$group = $_GET['g'];

		$this->js_root = $config['js_root'];
		$this->js_group = $groups[$group];
		$this->js_groups_file = $config['js_groups_file'];
		$this->cache_file = "{$this->js_root}cache/{$group}";
		$this->last_modified = $this->get_last_modified();

		$this->requested_time = (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
			? strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])
			: time();

		$this->cache_modified = (is_file($this->cache_file))
			? filemtime($this->cache_file)
			: 0;

		// Output some JS!
		$this->send();
	}

	protected function send()
	{
		// If the browser's cached version is up to date,
		// don't resend the file
		if($this->last_modified === $this->requested_time)
		{
			header('HTTP/1.1 304 Not Modified');
			exit();
		}

		//Determine what to do: rebuild cache, send files as is, or send cache.
		// If debug is set, just concatenate
		if(array_key_exists('debug', $_GET))
		{
			return $this->output($this->get_files());
		}
		else if($this->cache_modified < $this->last_modified)
		{
			$js = $this->minify($this->get_files());

			//Make sure cache file gets created/updated
			if(file_put_contents($this->cache_file, $js) === FALSE)
			{
				die('Cache file was not created. Make sure you have the correct folder permissions.');
			}

			return $this->output($js);
		}
		// Otherwise, send the cached file
		else
		{
			return $this->output(file_get_contents($cache_file));
		}
	}

	/**
	 * Makes a call to google closure compiler service
	 *
	 * @param array $options - Form parameters
	 * @return object
	 */
	protected function closure_call(array $options)
	{
		$client = new Client();
		$response = $client->post('http://closure-compiler.appspot.com/compile', [
			'headers' => [
				'Accept-Encoding' => 'gzip',
				'Content-type' => 'application/x-www-form-urlencoded'
			],
			'form_params' => $options
		]);

		return $response;
	}

	/**
	 * Do a call to the closure compiler to check for compilation errors
	 *
	 * @param  array $options
	 * @return void
	 */
	protected function check_minify_errors($options)
	{
		$error_res = $this->closure_call($options);
		$error_json = $error_res->getBody();
		$error_obj = json_decode($error_json) ?: (object)[];

		// Show error if exists
		if ( ! empty($error_obj->errors))
		{
			$error_json = json_encode($error_obj, JSON_PRETTY_PRINT);
			echo "console.error(${error_json});";
			die();
		}
	}

	/**
	 * Get Files
	 *
	 * Concatenates the javascript files for the current
	 * group as a string
	 *
	 * @return string
	 */
	protected function get_files()
	{
		$js = '';

		foreach($this->js_group as $file)
		{
			$new_file = realpath("{$this->js_root}{$file}");
			$js .= file_get_contents($new_file) . "\n\n";
		}

		return $js;
	}

	/**
	 * Get the most recent modified date
	 *
	 * @return int
	 */
	protected function get_last_modified()
	{
		$modified = array();

		foreach($this->js_group as $file)
		{
			$new_file = realpath("{$this->js_root}{$file}");
			$modified[] = filemtime($new_file);
		}

		//Add this page too, as well as the groups file
		$modified[] = filemtime(__FILE__);
		$modified[] = filemtime($this->js_groups_file);

		rsort($modified);
		$last_modified = $modified[0];

		return $last_modified;
	}

	/**
	 * Minifies javascript using google's closure compiler
	 *
	 * @param string $js
	 * @return string
	 */
	public function minify($js)
	{
		$options = [
			'output_info' => 'errors',
			'output_format' => 'json',
			'compilation_level' => 'SIMPLE_OPTIMIZATIONS',
			//'compilation_level' => 'ADVANCED_OPTIMIZATIONS',
			'js_code' => $js,
			'language' => 'ECMASCRIPT6_STRICT',
			'language_out' => 'ECMASCRIPT5_STRICT'
		];

		// Check for errors
		$this->check_minify_errors($options);

		// Now actually retrieve the compiled code
		$options['output_info'] = 'compiled_code';
		$res = $this->closure_call($options);
		$json = $res->getBody();
		$obj = json_decode($json);

		return $obj->compiledCode;
	}

	/**
	 * Output the minified javascript
	 *
	 * @param int $last_modified
	 * @param string $js
	 * @return void
	 */
	protected function output($js)
	{
		//This GZIPs the js for transmission to the user
		//making file size smaller and transfer rate quicker
		ob_start('ob_gzhandler');

		// Set important caching headers
		header('Content-Type: application/javascript; charset=utf8');
		header('Cache-control: public, max-age=691200, must-revalidate');
		header('Last-Modified: '.gmdate('D, d M Y H:i:s', $this->last_modified).' GMT');
		header('Expires: '.gmdate('D, d M Y H:i:s', (filemtime(__FILE__) + 691200)).' GMT');

		echo $js;

		ob_end_flush();
	}
}

// --------------------------------------------------------------------------
// ! Start Minifying
// --------------------------------------------------------------------------

$config = require_once('../app/config/minify_config.php');
$groups = require_once($config['js_groups_file']);
$cache_dir = "{$config['js_root']}cache";

if ( ! is_dir($cache_dir))
{
	mkdir($cache_dir);
}

if ( ! array_key_exists($_GET['g'], $groups))
{
	header('Content-Type: application/javascript; charset=utf8');
	echo '// You must specify a group that exists';
	die();
}

new JSMin($config, $groups);

//end of js.php