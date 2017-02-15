<?php declare(strict_types=1);
/**
 * Hummingbird Anime Client
 *
 * An API client for Hummingbird to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2016  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     3.1
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\EasyMin;

use function Amp\wait;
use Amp\Artax\{Client, FormBody, Request};
use Aviat\Ion\Json;

// Include guzzle
require_once('../vendor/autoload.php');
require_once('./min.php');

/**
 * Simple Javascript minfier, using google closure compiler
 */
class JSMin extends BaseMin {

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

		$this->cache_modified = (is_file($this->cache_file))
			? filemtime($this->cache_file)
			: 0;

		// Output some JS!
		$this->send();
	}

	protected function send()
	{
		// Override caching if debug key is set
		if($this->is_debug_call())
		{
			return $this->output($this->get_files());
		}

		// If the browser's cached version is up to date,
		// don't resend the file
		if($this->last_modified == $this->get_if_modified() && $this->is_not_debug())
		{
			throw new FileNotChangedException();
		}

		if($this->cache_modified < $this->last_modified)
		{
			$js = $this->minify($this->get_files());

			//Make sure cache file gets created/updated
			if (file_put_contents($this->cache_file, $js) === FALSE)
			{
				echo 'Cache file was not created. Make sure you have the correct folder permissions.';
				return;
			}

			return $this->output($js);
		}
		else
		{
			return $this->output(file_get_contents($this->cache_file));
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
		$formFields = http_build_query($options);
	
		$request = (new Request)
			->setMethod('POST')
			->setUri('http://closure-compiler.appspot.com/compile')
			->setAllHeaders([
				'Accept' => 'application/json',
				'Accept-Encoding' => 'gzip',
				'Content-type' => 'application/x-www-form-urlencoded'
			])
			->setBody($formFields);
		
		$response = wait((new Client)->request($request, [
			Client::OP_AUTO_ENCODING => false
		]));

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
		$error_obj = Json::decode($error_json) ?: (object)[];
		

		// Show error if exists
		if ( ! empty($error_obj->errors) || ! empty($error_obj->serverErrors))
		{
			$error_json = Json::encode($error_obj, JSON_PRETTY_PRINT);
			header('Content-type: application/javascript');
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
		$modified = [];

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
	protected function minify($js)
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
		$obj = Json::decode($json);

		//return $obj;
		return $obj['compiledCode'];
	}

	/**
	 * Output the minified javascript
	 *
	 * @param string $js
	 * @return void
	 */
	protected function output($js)
	{
		$this->send_final_output($js, 'application/javascript', $this->last_modified);
	}
}

// --------------------------------------------------------------------------
// ! Start Minifying
// --------------------------------------------------------------------------

$config = require_once('../app/appConf/minify_config.php');
$groups = require_once($config['js_groups_file']);
$cache_dir = "{$config['js_root']}cache";

if ( ! is_dir($cache_dir))
{
	mkdir($cache_dir);
}

if ( ! array_key_exists($_GET['g'], $groups))
{
	throw new InvalidArgumentException('You must specify a js group that exists');
}

try
{
	new JSMin($config, $groups);
}
catch (FileNotChangedException $e)
{
	BaseMin::send304();
}

//end of js.php