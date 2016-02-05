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

require_once('./min.php');

/**
 * Simple CSS Minifier
 */
class CSSMin extends BaseMin {

	protected $css_root;
	protected $path_from;
	protected $path_to;
	protected $group;
	protected $last_modified;
	protected $requested_time;

	public function __construct(array $config, array $groups)
	{
		$group = $_GET['g'];
		$this->css_root = $config['css_root'];
		$this->path_from = $config['path_from'];
		$this->path_to = $config['path_to'];
		$this->group = $groups[$group];
		$this->last_modified = $this->get_last_modified();

		$this->requested_time = $this->get_if_modified();
	}

	public function __destruct()
	{
		$this->send();
	}

	/**
	 * Send the CSS
	 *
	 * @return void
	 */
	protected function send()
	{
		/*if($this->last_modified >= $this->requested_time)
		{
			header('304 Not Modified');
			die();
		}*/

		$css = ( ! array_key_exists('debug', $_GET))
			? $this->compress($this->get_css())
			: $this->get_css();

		$this->output($css);
	}

	/**
	 * Function for compressing the CSS as tightly as possible
	 *
	 * @param string $buffer
	 * @return string
	 */
	public function compress($buffer)
	{
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

	/**
	 * Get the most recent file modification date
	 *
	 * @return int
	 */
	protected function get_last_modified()
	{
		$modified = array();

		// Get all the css files, and concatenate them together
		if(isset($this->group))
		{
			foreach($this->group as $file)
			{
				$new_file = realpath("{$this->css_root}{$file}");
				$modified[] = filemtime($new_file);
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
	protected function get_css()
	{
		$css = '';

		foreach($this->group as $file)
		{
			$new_file = realpath("{$this->css_root}{$file}");
			$css .= file_get_contents($new_file);
		}

		// Correct paths that have changed due to concatenation
		// based on rules in the config file
		// $css = str_replace($this->path_from, $this->path_to, $css);

		return $css;
	}

	/**
	 * Output the CSS
	 *
	 * @return void
	 */
	public function output($css)
	{
		//This GZIPs the CSS for transmission to the user
		//making file size smaller and transfer rate quicker
		ob_start("ob_gzhandler");

		header("Content-Type: text/css; charset=utf8");
		header("Cache-control: public, max-age=691200, must-revalidate");
		header("Last-Modified: ".gmdate('D, d M Y H:i:s', $this->last_modified)." GMT");
		header("Expires: ".gmdate('D, d M Y H:i:s', (filemtime(basename(__FILE__)) + 691200))." GMT");

		echo $css;

		ob_end_flush();
	}
}

// --------------------------------------------------------------------------
// ! Start Minifying
// --------------------------------------------------------------------------

//Get config files
$config = require('../app/config/minify_config.php');
$groups = require($config['css_groups_file']);

new CSSMin($config, $groups);

//End of css.php