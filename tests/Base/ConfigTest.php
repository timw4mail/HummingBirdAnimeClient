<?php

use \AnimeClient\Base\Config;

class ConfigTest extends AnimeClient_TestCase {

	public function setUp()
	{
		$this->config = new Config([
			'foo' => 'bar',
			'asset_path' => '//localhost/assets/',
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

	public function dataFullUrl()
	{
		return [
			'default_view' => [
				'config' => [
					'routing' => [
						'anime_path' => 'anime',
						'manga_path' => 'manga',
						'default_list' => 'manga',
						'default_anime_path' => '/anime/watching',
						'default_manga_path' => '/manga/all',
						'default_to_list_view' => FALSE,
					],
				],
				'path' => '',
				'type' => 'manga',
				'expected' => '//localhost/manga/all',
			],
			'default_view_list' => [
				'config' => [
					'routing' => [
						'anime_path' => 'anime',
						'manga_path' => 'manga',
						'default_list' => 'manga',
						'default_anime_path' => '/anime/watching',
						'default_manga_path' => '/manga/all',
						'default_to_list_view' => TRUE,
					],
				],
				'path' => '',
				'type' => 'manga',
				'expected' => '//localhost/manga/all/list',
			]
		];
	}

	/**
	 * @dataProvider dataFullUrl
	 */
	public function testFullUrl($config, $path, $type, $expected)
	{
		$this->config = new Config($config);

		$result = $this->config->full_url($path, $type);

		$this->assertEquals($expected, $result);
	}

	public function dataBaseUrl()
	{
		$config = [
			'routing' => [
				'anime_path' => 'anime',
				'manga_path' => 'manga',
				'default_list' => 'manga',
				'default_anime_path' => '/watching',
				'default_manga_path' => '/all',
				'default_to_list_view' => TRUE,
			],
		];

		return [
			'path_based_routing_anime' => [
				'config' => $config,
				'type' => 'anime',
				'expected' => '//localhost/anime'
			],
			'path_based_routing_manga' => [
				'config' => $config,
				'type' => 'manga',
				'expected' => '//localhost/manga'
			]
		];
	}

	/**
	 * @dataProvider dataBaseUrl
	 */
	public function testBaseUrl($config, $type, $expected)
	{
		$this->config = new Config($config);
		$result = $this->config->base_url($type);

		$this->assertEquals($expected, $result);
	}
}