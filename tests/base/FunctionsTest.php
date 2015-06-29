<?php

use \AnimeClient\Config;

class FunctionsTest extends AnimeClient_TestCase {

	public function setUp()
	{
		parent::setUp();

		global $config;
		$config = new Config([
			'config' => [
				'asset_path' => '//localhost/assets/'
			],
			'base_config' => [

			]
		]);
	}

	/**
	 * Basic sanity test for _dir function
	 */
	public function testDir()
	{
		$this->assertEquals('foo' . DIRECTORY_SEPARATOR . 'bar', _dir('foo', 'bar'));
	}

	public function testIsSelected()
	{
		// Failure to match
		$this->assertEquals('', is_selected('foo', 'bar'));

		// Matches
		$this->assertEquals('selected', is_selected('foo', 'foo'));
	}

	public function testIsNotSelected()
	{
		// Failure to match
		$this->assertEquals('selected', is_not_selected('foo', 'bar'));

		// Matches
		$this->assertEquals('', is_not_selected('foo', 'foo'));
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
		$result = call_user_func_array('asset_url', $args);

		$this->assertEquals($expected, $result);
	}

	public function testIsLoggedIn()
	{
		$this->assertFalse(is_logged_in());

		$_SESSION['hummingbird_anime_token'] = 'foobarbadsessionid';

		$this->assertTrue(is_logged_in());
	}
}