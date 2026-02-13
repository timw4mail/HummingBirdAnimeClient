<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.4
 *
 * @copyright   2015 - 2026  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.3
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

	#[\Override]
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

	public static function dataIsViewPage()
	{
		return [
			[
				'uri' => '/anime/update',
				'expected' => false,
			],
			[
				'uri' => '/anime/watching',
				'expected' => true,
			],
			[
				'uri' => '/manga/reading',
				'expected' => true,
			],
			[
				'uri' => '/manga/update',
				'expected' => false,
			],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('dataIsViewPage')]
	public function testIsViewPage(mixed $uri, mixed $expected)
	{
		$this->setSuperGlobals([
			'_SERVER' => [
				'REQUEST_URI' => $uri,
			],
		]);
		$this->assertSame($expected, $this->util->isViewPage());
	}

	public function testAriaCurrent(): void
	{
		$this->assertSame('true', Util::ariaCurrent(true));
		$this->assertSame('false', Util::ariaCurrent(false));
	}

	public function testEq(): void
	{
		$this->assertTrue(Util::eq(1, 1));
		$this->assertFalse(Util::eq(1, '1'));
	}
}
