<?php

use \Aviat\AnimeClient\Base\Config;

class ConfigTest extends AnimeClient_TestCase {

	public function setUp()
	{
		$this->config = new Config([
			'foo' => 'bar',
			'asset_path' => '/assets',
			'bar' => 'baz'
		]);
	}

	public function testConfig__get()
	{
		$this->assertEquals($this->config->foo, $this->config->__get('foo'));
		$this->assertEquals($this->config->bar, $this->config->__get('bar'));
		$this->assertEquals(NULL, $this->config->baz);
	}

	public function testGetNonExistentConfigItem()
	{
		$this->assertEquals(NULL, $this->config->foobar);
	}
}