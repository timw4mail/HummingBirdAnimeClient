<?php

use \AnimeClient\Config;

class ConfigTest extends AnimeClient_TestCase {

	public function setUp()
	{
		$this->config = new Config([
			'config' => [
				'foo' => 'bar',
				'asset_path' => '//localhost/assets/'
			],
			'base_config' => [
				'bar' => 'baz'
			]
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

	public function assetUrlProvider()
	{
		return [
			'single argument' => [
				'args' => [
					'images'
				],
				'expected' => '//localhost/assets/images',
			],
			'multiple arguments' => [
				'args' => [
					'images', 'anime', 'foo.png'
				],
				'expected' => '//localhost/assets/images/anime/foo.png'
			]
		];
	}

	/**
	 * @dataProvider assetUrlProvider
	 */
	public function testAssetUrl($args, $expected)
	{
		$result = call_user_func_array([$this->config, 'asset_url'], $args);

		$this->assertEquals($expected, $result);
	}

	public function fullUrlProvider()
	{
		return [
			'default_view' => [
				'config' => [
					'anime_host' => '',
					'manga_host' => '',
					'anime_path' => 'anime',
					'manga_path' => 'manga',
					'route_by' => 'host',
					'default_list' => 'manga',
					'default_anime_path' => '/watching',
					'default_manga_path' => '/all',
					'default_to_list_view' => FALSE,
				],
				'path' => '',
				'type' => 'manga',
				'expected' => '//localhost/manga/all',
			],
			'default_view_list' => [
				'config' => [
					'anime_host' => '',
					'manga_host' => '',
					'anime_path' => 'anime',
					'manga_path' => 'manga',
					'route_by' => 'host',
					'default_list' => 'manga',
					'default_anime_path' => '/watching',
					'default_manga_path' => '/all',
					'default_to_list_view' => TRUE,
				],
				'path' => '',
				'type' => 'manga',
				'expected' => '//localhost/manga/all/list',
			]
		];
	}

	/**
	 * @dataProvider fullUrlProvider
	 */
	public function testFullUrl($config, $path, $type, $expected)
	{
		$this->config = new Config(['config' => $config, 'base_config' => []]);

		$result = $this->config->full_url($path, $type);

		$this->assertEquals($expected, $result);
	}
}