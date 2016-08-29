<?php

namespace Aviat\AnimeClient\Tests;

use Aviat\AnimeClient\Util;

class UtilTest extends \AnimeClient_TestCase {

	public function setUp()
	{
		parent::setUp();
		$this->util = new Util($this->container);
	}

	public function testIsSelected()
	{
		// Failure to match
		$this->assertEquals('', Util::is_selected('foo', 'bar'));

		// Matches
		$this->assertEquals('selected', Util::is_selected('foo', 'foo'));
	}

	public function testIsNotSelected()
	{
		// Failure to match
		$this->assertEquals('selected', Util::is_not_selected('foo', 'bar'));

		// Matches
		$this->assertEquals('', Util::is_not_selected('foo', 'foo'));
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
		$this->assertEquals($expected, $this->util->is_view_page());
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
		$this->assertEquals(!$expected, $this->util->is_form_page());
	}
}
