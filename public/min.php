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

class FileNotChangedException extends \Exception {}
class BaseMin {

	/**
	 * Get value of the if-modified-since header
	 *
	 * @return int - timestamp to compare for cache control
	 */
	protected function get_if_modified()
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
	protected function get_if_none_match()
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
	protected function is_not_debug()
	{
		return ! $this->is_debug_call();
	}

	/**
	 * Determine whether or not to send debug version
	 *
	 * @return boolean
	 */
	protected function is_debug_call()
	{
		return array_key_exists('debug', $_GET);
	}

	/**
	 * Send actual output to browser
	 *
	 * @param string $content - the body of the response
	 * @param string $mime_type - the content type
	 * @param int $last_modified - the last modified date
	 * @return void
	 */
	protected function send_final_output($content, $mime_type, $last_modified)
	{
		//This GZIPs the CSS for transmission to the user
		//making file size smaller and transfer rate quicker
		ob_start("ob_gzhandler");

		$expires = $last_modified + 691200;
		$last_modified_date = gmdate('D, d M Y H:i:s', $last_modified);
		$expires_date = gmdate('D, d M Y H:i:s', $expires);

		header("Content-Type: {$mime_type}; charset=utf8");
		header("Cache-control: public, max-age=691200, must-revalidate");
		header("Last-Modified: {$last_modified_date} GMT");
		header("Expires: {$expires_date} GMT");

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