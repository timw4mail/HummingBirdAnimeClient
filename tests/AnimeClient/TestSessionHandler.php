<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @copyright   2015 - 2022  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshome.page/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests;

use SessionHandlerInterface;

class TestSessionHandler implements SessionHandlerInterface
{
	public $data = [];
	public $savePath = './test_data/sessions';

	public function close()
	{
		return TRUE;
	}

	public function destroy($id)
	{
		$file = "{$this->savePath}/{$id}";
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
		return json_decode(@file_get_contents("{$this->savePath}/{$id}"), TRUE, 512, JSON_THROW_ON_ERROR);
	}

	public function write($id, $data)
	{
		$file = "{$this->savePath}/{$id}";
		file_put_contents($file, json_encode($data, JSON_THROW_ON_ERROR));

		return TRUE;
	}
}
// End of TestSessionHandler.php
