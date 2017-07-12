<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2017  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\EasyMin;

use function Amp\wait;
use Amp\Artax\{Client, FormBody, Request};
use Aviat\Ion\Json;

// Include Amp and Artax
require_once('../vendor/autoload.php');

//Creative rewriting of /g/groupname to ?g=groupname
$pi = $_SERVER['PATH_INFO'];
$pia = explode('/', $pi);

$piaLen = count($pia);
$i = 1;

while($i < $piaLen)
{
	$j = $i+1;
	$j = (isset($pia[$j])) ? $j : $i;

	$_GET[$pia[$i]] = $pia[$j];

	$i = $j + 1;
};

class FileNotChangedException extends \Exception {}

/**
 * Simple Javascript minfier, using google closure compiler
 */
class JSMin {

	protected $jsRoot;
	protected $jsGroup;
	protected $configFile;
	protected $cacheFile;

	protected $lastModified;
	protected $requestedTime;
	protected $cacheModified;

	public function __construct(array $config, string $configFile)
	{
		$group = $_GET['g'];
		$groups = $config['groups'];

		$this->jsRoot = $config['js_root'];
		$this->jsGroup = $groups[$group];
		$this->configFile = $configFile;
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
		$modified[] = filemtime($this->configFile);

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

		/**
	 * Get value of the if-modified-since header
	 *
	 * @return int - timestamp to compare for cache control
	 */
	protected function getIfModified()
	{
		return (array_key_exists('HTTP_IF_MODIFIED_SINCE', $_SERVER))
			? strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])
			: time();
	}

	/**
	 * Get value of etag to compare to hash of output
	 *
	 * @return string - the etag to compare
	 */
	protected function getIfNoneMatch()
	{
		return (array_key_exists('HTTP_IF_NONE_MATCH', $_SERVER))
			? $_SERVER['HTTP_IF_NONE_MATCH']
			: '';
	}

	/**
	 * Determine whether or not to send debug version
	 *
	 * @return boolean
	 */
	protected function isNotDebug()
	{
		return ! $this->isDebugCall();
	}

	/**
	 * Determine whether or not to send debug version
	 *
	 * @return boolean
	 */
	protected function isDebugCall()
	{
		return array_key_exists('debug', $_GET);
	}

	/**
	 * Send actual output to browser
	 *
	 * @param string $content - the body of the response
	 * @param string $mimeType - the content type
	 * @param int $lastModified - the last modified date
	 * @return void
	 */
	protected function sendFinalOutput($content, $mimeType, $lastModified)
	{
		//This GZIPs the CSS for transmission to the user
		//making file size smaller and transfer rate quicker
		ob_start("ob_gzhandler");

		$expires = $lastModified + 691200;
		$lastModifiedDate = gmdate('D, d M Y H:i:s', $lastModified);
		$expiresDate = gmdate('D, d M Y H:i:s', $expires);

		header("Content-Type: {$mimeType}; charset=utf8");
		header("Cache-control: public, max-age=691200, must-revalidate");
		header("Last-Modified: {$lastModifiedDate} GMT");
		header("Expires: {$expiresDate} GMT");

		echo $content;

		ob_end_flush();
	}

	/**
	 * Send a 304 Not Modified header
	 *
	 * @return void
	 */
	public static function send304()
	{
		header("status: 304 Not Modified", true, 304);
	}
}

// --------------------------------------------------------------------------
// ! Start Minifying
// --------------------------------------------------------------------------

$configFile = realpath(__DIR__ . '/../app/appConf/minify_config.php');
$config = require_once($configFile);
$groups = $config['groups'];
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
	new JSMin($config, $configFile);
}
catch (FileNotChangedException $e)
{
	JSMin::send304();
}

//end of js.php