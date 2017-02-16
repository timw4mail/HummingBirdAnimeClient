<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2017  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests;

use Aviat\AnimeClient\Util;

class UtilTest extends AnimeClientTestCase {

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
