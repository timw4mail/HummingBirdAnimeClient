<?php

class ConfigTest extends AnimeClient_TestCase {

	public function setUp()
	{
		$this->config = new Config([
			'config' => [
				'foo' => 'bar'
			],
			'base_config' => [
				'bar' => 'baz'
			]
		]);
	}

	public function testConfig__get()
	{
		$this->assertEquals($this->config->bar, $this->config->__get('bar'));
	}

	public function testGetNonExistentConfigItem()
	{
		$this->assertEquals(NULL, $this->config->foobar);
	}

}