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
class BaseMin {

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