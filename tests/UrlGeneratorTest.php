<?php

namespace Aviat\AnimeClient\Tests;

use Aviat\AnimeClient\UrlGenerator;
use Aviat\Ion\Config;

class UrlGeneratorTest extends AnimeClientTestCase {

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
		$urlGenerator = new UrlGenerator($this->container);

		$result = $urlGenerator->assetUrl(...$args);
		$this->assertEquals($expected, $result);
	}

	public function dataFullUrl()
	{
		return [
			'default_view' => [
				'config' => [
					'routes' => [
						'routes' => [],
						'route_config' => [
							'anime_path' => 'anime',
							'manga_path' => 'manga',
							'default_list' => 'manga',
							'default_anime_path' => '/anime/watching',
							'default_manga_path' => '/manga/all',
							'default_to_list_view' => FALSE,
						]
					],
				],
				'path' => '',
				'type' => 'manga',
				'expected' => '//localhost/manga/all',
			],
			'default_view_list' => [
				'config' => [
					'routes' => [
						'routes' => [],
						'route_config' => [
							'anime_path' => 'anime',
							'manga_path' => 'manga',
							'default_list' => 'manga',
							'default_anime_path' => '/anime/watching',
							'default_manga_path' => '/manga/all',
							'default_to_list_view' => TRUE,
						]
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
		$config = new Config($config);
		$this->container->setInstance('config', $config);
		$urlGenerator = new UrlGenerator($this->container);

		$result = $urlGenerator->fullUrl($path, $type);

		$this->assertEquals($expected, $result);
	}
}