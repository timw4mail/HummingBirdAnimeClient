<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.3
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests;

use Aviat\AnimeClient\Util;

class UtilTest extends AnimeClientTestCase {

	protected $util;

	public function setUp(): void	{
		parent::setUp();
		$this->util = new Util($this->container);
	}

	public function testIsSelected()
	{
		// Failure to match
		$this->assertEquals('', Util::isSelected('foo', 'bar'));

		// Matches
		$this->assertEquals('selected', Util::isSelected('foo', 'foo'));
	}

	public function testIsNotSelected()
	{
		// Failure to match
		$this->assertEquals('selected', Util::isNotSelected('foo', 'bar'));

		// Matches
		$this->assertEquals('', Util::isNotSelected('foo', 'foo'));
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
		$this->assertEquals($expected, $this->util->isViewPage());
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
		$this->assertEquals(!$expected, $this->util->isFormPage());
	}
}
