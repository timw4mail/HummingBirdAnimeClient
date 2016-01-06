<?php

use Aviat\AnimeClient\AnimeClient;


class AnimeClientTest extends AnimeClient_TestCase {

	public function setUp()
	{
		parent::setUp();
		$this->anime_client = new AnimeClient();
		$this->anime_client->setContainer($this->container);
	}

	/**
	 * Basic sanity test for _dir function
	 */
	public function testDir()
	{
		$this->assertEquals('foo' . DIRECTORY_SEPARATOR . 'bar', \_dir('foo', 'bar'));
	}

	public function testIsSelected()
	{
		// Failure to match
		$this->assertEquals('', AnimeClient::is_selected('foo', 'bar'));

		// Matches
		$this->assertEquals('selected', AnimeClient::is_selected('foo', 'foo'));
	}

	public function testIsNotSelected()
	{
		// Failure to match
		$this->assertEquals('selected', AnimeClient::is_not_selected('foo', 'bar'));

		// Matches
		$this->assertEquals('', AnimeClient::is_not_selected('foo', 'foo'));
	}

	public function dataIsViewPage()
	{
		return [
			[
				'uri' => '/anime/update',
				'expected' => FALSE
			],
			[
				'uri' => '/anime/watching',
				'expected' => TRUE
			],
			[
				'uri' => '/manga/reading',
				'expected' => TRUE
			],
			[
				'uri' => '/manga/update',
				'expected' => FALSE
			]
		];
	}

	/**
	 * @dataProvider dataIsViewPage
	 */
	public function testIsViewPage($uri, $expected)
	{
		$this->setSuperGlobals([
			'_SERVER' => [
				'REQUEST_URI' => $uri
			]
		]);
		$this->assertEquals($expected, $this->anime_client->is_view_page());
	}

	/**
	 * @dataProvider dataIsViewPage
	 */
	public function testIsFormPage($uri, $expected)
	{
		$this->setSuperGlobals([
			'_SERVER' => [
				'REQUEST_URI' => $uri
			]
		]);
		$this->assertEquals(!$expected, $this->anime_client->is_form_page());
	}
}
