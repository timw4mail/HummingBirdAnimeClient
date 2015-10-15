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

		$this->assertNull($this->config->get(['apple','sauce']));
	}

	public function testConfigSet()
	{
		$this->config->set('foo', 'foobar');
		$this->assertEquals('foobar', $this->config->get('foo'));

		$this->config->set(['apple', 'sauce', 'is'], 'great');
		$this->assertEquals('great', $this->config->get(['apple', 'sauce', 'is']));
	}

	public function dataConfigDelete()
	{
		return [
			'top level delete' => [
				'key' => 'apple',
				'assertKeys' => [
					[
						'path' => ['apple', 'sauce', 'is'],
						'expected' => NULL
					],
					[
						'path' => ['apple', 'sauce'],
						'expected' => NULL
					],
					[
						'path' => 'apple',
						'expected' => NULL
					]
				]
			],
			/*'mid level delete' => [
				'key' => ['apple', 'sauce'],
				'assertKeys' => [
					[
						'path' => ['apple', 'sauce', 'is'],
						'expected' => NULL
					],
					[
						'path' => ['apple', 'sauce'],
						'expected' => NULL
					],
					[
						'path' => 'apple',
						'expected' => []
					]
				]
			],
			'deep delete' => [
				'key' => ['apple', 'sauce', 'is'],
				'assertKeys' => [
					[
						'path' => ['apple', 'sauce', 'is'],
						'expected' => NULL
					],
					[
						'path' => ['apple', 'sauce'],
						'expected' => NULL
					]
				]
			]*/
		];
	}

	/**
	 * @dataProvider dataConfigDelete
	 */
	public function testConfigDelete($key, $assertKeys)
	{
		$this->config->set(['apple', 'sauce', 'is'], 'great');
		$this->config->delete($key);

		foreach($assertKeys as $pair)
		{
			$this->assertEquals($pair['expected'], $this->config->get($pair['path']));
		}
	}

	public function testGetNonExistentConfigItem()
	{
		$this->assertNull($this->config->get('foobar'));
	}
}