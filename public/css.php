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

		$this->send();
	}

	/**
	 * Send the CSS
	 *
	 * @return void
	 */
	protected function send()
	{
		if($this->last_modified >= $this->get_if_modified() && $this->is_not_debug())
		{
			throw new FileNotChangedException();
		}

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
	protected function compress($buffer)
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
		$css = str_replace($this->path_from, $this->path_to, $css);

		return $css;
	}

	/**
	 * Output the CSS
	 *
	 * @return void
	 */
	protected function output($css)
	{
		$this->send_final_output($css, 'text/css', $this->last_modified);
	}
}

// --------------------------------------------------------------------------
// ! Start Minifying
// --------------------------------------------------------------------------

//Get config files
$config = require('../app/config/minify_config.php');
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