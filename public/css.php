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

require_once('./min.php');

/**
 * Simple CSS Minifier
 */
class CSSMin extends BaseMin {

	protected $cssRoot;
	protected $pathFrom;
	protected $pathTo;
	protected $group;
	protected $lastModified;
	protected $requestedTime;

	public function __construct(array $config, array $groups)
	{
		$group = $_GET['g'];
		$this->cssRoot = $config['css_root'];
		$this->pathFrom = $config['path_from'];
		$this->pathTo = $config['path_to'];
		$this->group = $groups[$group];
		$this->lastModified = $this->getLastModified();

		$this->send();
	}

	/**
	 * Send the CSS
	 *
	 * @return void
	 */
	protected function send()
	{
		if($this->lastModified >= $this->getIfModified() && $this->isNotDebug())
		{
			throw new FileNotChangedException();
		}

		$css = ( ! array_key_exists('debug', $_GET))
			? $this->compress($this->getCss())
			: $this->getCss();

		$this->output($css);
	}

	/**
	 * Function for compressing the CSS as tightly as possible
	 *
	 * @param string $buffer
	 * @return string
	 */
	protected function compress($buffer)
	{
		//Remove CSS comments
		$buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);

		//Remove tabs, spaces, newlines, etc.
		$buffer = preg_replace('`\s+`', ' ', $buffer);
		$replace = [
			' )' => ')',
			') ' => ')',
			' }' => '}',
			'} ' => '}',
			' {' => '{',
			'{ ' => '{',
			', ' => ',',
			': ' => ':',
			'; ' => ';',
		];

		//Eradicate every last space!
		$buffer = trim(strtr($buffer, $replace));
		$buffer = str_replace('{ ', '{', $buffer);
		$buffer = str_replace('} ', '}', $buffer);

		return $buffer;
	}

	/**
	 * Get the most recent file modification date
	 *
	 * @return int
	 */
	protected function getLastModified()
	{
		$modified = [];

		// Get all the css files, and concatenate them together
		if(isset($this->group))
		{
			foreach($this->group as $file)
			{
				$newFile = realpath("{$this->cssRoot}{$file}");
				$modified[] = filemtime($newFile);
			}
		}

		//Add this page for last modified check
		$modified[] = filemtime(__FILE__);

		//Get the latest modified date
		rsort($modified);

		return array_shift($modified);
	}

	/**
	 * Get the css to display
	 *
	 * @return string
	 */
	protected function getCss()
	{
		$css = '';

		foreach($this->group as $file)
		{
			$newFile = realpath("{$this->cssRoot}{$file}");
			$css .= file_get_contents($newFile);
		}

		// Correct paths that have changed due to concatenation
		// based on rules in the config file
		$css = str_replace($this->pathFrom, $this->pathTo, $css);

		return $css;
	}

	/**
	 * Output the CSS
	 *
	 * @return void
	 */
	protected function output($css)
	{
		$this->sendFinalOutput($css, 'text/css', $this->lastModified);
	}
}

// --------------------------------------------------------------------------
// ! Start Minifying
// --------------------------------------------------------------------------

//Get config files
$config = require('../app/appConf/minify_config.php');
$groups = require($config['css_groups_file']);

if ( ! array_key_exists($_GET['g'], $groups))
{
	throw new InvalidArgumentException('You must specify a css group that exists');
}

try
{
	new CSSMin($config, $groups);
}
catch (FileNotChangedException $e)
{
	BaseMin::send304();
}

//End of css.php