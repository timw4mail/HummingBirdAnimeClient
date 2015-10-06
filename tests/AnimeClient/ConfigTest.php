<?php

use \Aviat\AnimeClient\Config;

class ConfigTest extends AnimeClient_TestCase {

	public function setUp()
	{
		$this->config = new Config([
			'foo' => 'bar',
			'asset_path' => '/assets',
			'bar' => 'baz'
		]);
	}

	public function testConfigGet()
	{
		$this->assertEquals('bar', $this->config->get('foo'));
		$this->assertEquals('baz', $this->config->get('bar'));
		$this->assertNull($this->config->get('baz'));
	}

	public function testGetNonExistentConfigItem()
	{
		$this->assertNull($this->config->get('foobar'));
	}
}