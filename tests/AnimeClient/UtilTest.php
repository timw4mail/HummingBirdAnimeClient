<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshome.page>
 * @copyright   2015 - 2022  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests;

use Aviat\AnimeClient\Util;

/**
 * @internal
 */
final class UtilTest extends AnimeClientTestCase
{
	protected $util;

	protected function setUp(): void
	{
		parent::setUp();
		$this->util = new Util($this->container);
	}

	public function testIsSelected()
	{
		// Failure to match
		$this->assertSame('', Util::isSelected('foo', 'bar'));

		// Matches
		$this->assertSame('selected', Util::isSelected('foo', 'foo'));
	}

	public function testIsNotSelected()
	{
		// Failure to match
		$this->assertSame('selected', Util::isNotSelected('foo', 'bar'));

		// Matches
		$this->assertSame('', Util::isNotSelected('foo', 'foo'));
	}

	public function dataIsViewPage()
	{
		return [
			[
				'uri' => '/anime/update',
				'expected' => FALSE,
			],
			[
				'uri' => '/anime/watching',
				'expected' => TRUE,
			],
			[
				'uri' => '/manga/reading',
				'expected' => TRUE,
			],
			[
				'uri' => '/manga/update',
				'expected' => FALSE,
			],
		];
	}

	/**
	 * @dataProvider dataIsViewPage
	 * @param mixed $uri
	 * @param mixed $expected
	 */
	public function testIsViewPage($uri, $expected)
	{
		$this->setSuperGlobals([
			'_SERVER' => [
				'REQUEST_URI' => $uri,
			],
		]);
		$this->assertSame($expected, $this->util->isViewPage());
	}

	/**
	 * @dataProvider dataIsViewPage
	 * @param mixed $uri
	 * @param mixed $expected
	 */
	public function testIsFormPage($uri, $expected)
	{
		$this->setSuperGlobals([
			'_SERVER' => [
				'REQUEST_URI' => $uri,
			],
		]);
		$this->assertSame( ! $expected, $this->util->isFormPage());
	}

	public function testAriaCurrent(): void
	{
		$this->assertSame('true', Util::ariaCurrent(TRUE));
		$this->assertSame('false', Util::ariaCurrent(FALSE));
	}
}
