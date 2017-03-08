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
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests;

class TestSessionHandler implements \SessionHandlerInterface {
	
	public $data = [];
	public $savePath = './test_data/sessions';
	
	public function close() 
	{
		return TRUE;
	}
	
	public function destroy($id) 
	{
		$file = "$this->savePath/$id";
		if (file_exists($file))
		{
			@unlink($file);
		}
		$this->data[$id] = [];
		return TRUE;
	}
	
	public function gc($maxLifetime)
	{
		return TRUE;
	}
	
	public function open($savePath, $name) 
	{
		/*if ( ! array_key_exists($savePath, $this->data))
		{
			$this->savePath = $savePath;
			$this->data = [];
		}*/
		return TRUE;
	}
	
	public function read($id) 
	{
		return json_decode(@file_get_contents("$this->savePath/$id"), TRUE);
	}
	
	public function write($id, $data) 
	{
		$file = "$this->savePath/$id";
		file_put_contents($file, json_encode($data));
		
		return TRUE;
	}
	
}
// End of TestSessionHandler.php