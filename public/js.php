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

	protected $jsRoot;
	protected $jsGroup;
	protected $jsGroupsFile;
	protected $cacheFile;

	protected $lastModified;
	protected $requestedTime;
	protected $cacheModified;

	public function __construct(array $config, array $groups)
	{
		$group = $_GET['g'];

		$this->jsRoot = $config['js_root'];
		$this->jsGroup = $groups[$group];
		$this->jsGroupsFile = $config['js_groups_file'];
		$this->cacheFile = "{$this->jsRoot}cache/{$group}";
		$this->lastModified = $this->getLastModified();

		$this->cacheModified = (is_file($this->cacheFile))
			? filemtime($this->cacheFile)
			: 0;

		// Output some JS!
		$this->send();
	}

	protected function send()
	{
		// Override caching if debug key is set
		if($this->isDebugCall())
		{
			return $this->output($this->getFiles());
		}

		// If the browser's cached version is up to date,
		// don't resend the file
		if($this->lastModified == $this->getIfModified() && $this->isNotDebug())
		{
			throw new FileNotChangedException();
		}

		if($this->cacheModified < $this->lastModified)
		{
			$js = $this->minify($this->getFiles());

			//Make sure cache file gets created/updated
			if (file_put_contents($this->cacheFile, $js) === FALSE)
			{
				echo 'Cache file was not created. Make sure you have the correct folder permissions.';
				return;
			}

			return $this->output($js);
		}
		else
		{
			return $this->output(file_get_contents($this->cacheFile));
		}
	}

	/**
	 * Makes a call to google closure compiler service
	 *
	 * @param array $options - Form parameters
	 * @return object
	 */
	protected function closureCall(array $options)
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
	protected function checkMinifyErrors($options)
	{
		$errorRes = $this->closureCall($options);
		$errorJson = $errorRes->getBody();
		$errorObj = Json::decode($errorJson) ?: (object)[];
		

		// Show error if exists
		if ( ! empty($errorObj->errors) || ! empty($errorObj->serverErrors))
		{
			$errorJson = Json::encode($errorObj, JSON_PRETTY_PRINT);
			header('Content-type: application/javascript');
			echo "console.error(${errorJson});";
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
	protected function getFiles()
	{
		$js = '';

		foreach($this->jsGroup as $file)
		{
			$newFile = realpath("{$this->jsRoot}{$file}");
			$js .= file_get_contents($newFile) . "\n\n";
		}

		return $js;
	}

	/**
	 * Get the most recent modified date
	 *
	 * @return int
	 */
	protected function getLastModified()
	{
		$modified = [];

		foreach($this->jsGroup as $file)
		{
			$newFile = realpath("{$this->jsRoot}{$file}");
			$modified[] = filemtime($newFile);
		}

		//Add this page too, as well as the groups file
		$modified[] = filemtime(__FILE__);
		$modified[] = filemtime($this->jsGroupsFile);

		rsort($modified);
		$lastModified = $modified[0];

		return $lastModified;
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
		$this->checkMinifyErrors($options);

		// Now actually retrieve the compiled code
		$options['output_info'] = 'compiled_code';
		$res = $this->closureCall($options);
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
		$this->sendFinalOutput($js, 'application/javascript', $this->lastModified);
	}
}

// --------------------------------------------------------------------------
// ! Start Minifying
// --------------------------------------------------------------------------

$config = require_once('../app/appConf/minify_config.php');
$groups = require_once($config['js_groups_file']);
$cacheDir = "{$config['js_root']}cache";

if ( ! is_dir($cacheDir))
{
	mkdir($cacheDir);
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