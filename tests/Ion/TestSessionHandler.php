<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshome.page>
 * @copyright   2015 - 2022  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\Ion\Tests;

use SessionHandlerInterface;

class TestSessionHandler implements SessionHandlerInterface
{
	public $data = [];
	public $save_path = './test_data/sessions';

	public function close()
	{
		return TRUE;
	}

	public function destroy($id)
	{
		$file = "{$this->save_path}/{$id}";
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

	public function open($save_path, $name)
	{
		/*if ( ! array_key_exists($save_path, $this->data))
		{
			$this->save_path = $save_path;
			$this->data = [];
		}*/
		return TRUE;
	}

	public function read($id)
	{
		return json_decode(@file_get_contents("{$this->save_path}/{$id}"), TRUE);
	}

	public function write($id, $data)
	{
		$file = "{$this->save_path}/{$id}";
		file_put_contents($file, json_encode($data));

		return TRUE;
	}
}
// End of TestSessionHandler.php
