<?php

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