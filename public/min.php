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

class BaseMin {
	public function get_if_modified()
	{
		return (array_key_exists('HTTP_IF_MODIFIED_SINCE', $_SERVER))
			? strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE'])
			: time();
	}

	public function get_if_none_match()
	{
		return (array_key_exists('HTTP_IF_NONE_MATCH', $_SERVER))
			? $_SERVER['HTTP_IF_NONE_MATCH']
			: '';
	}
}