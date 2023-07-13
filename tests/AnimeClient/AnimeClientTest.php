<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.1
 *
 * @copyright   2015 - 2023  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests;

use DateTime;
use PHPUnit\Framework\Attributes\IgnoreFunctionForCodeCoverage;
use function Aviat\AnimeClient\{arrayToToml, checkFolderPermissions, clearCache, colNotEmpty, friendlyTime, getLocalImg, getResponse, isSequentialArray, tomlToArray};
use const Aviat\AnimeClient\{MINUTES_IN_DAY, MINUTES_IN_HOUR, MINUTES_IN_YEAR, SECONDS_IN_MINUTE};

/**
 * @internal
 */
#[IgnoreFunctionForCodeCoverage('Aviat\AnimeClient\loadConfig')]
#[IgnoreFunctionForCodeCoverage('Aviat\AnimeClient\createPlaceholderImage')]
#[IgnoreFunctionForCodeCoverage('Aviat\AnimeClient\renderTemplate')]
#[IgnoreFunctionForCodeCoverage('Aviat\AnimeClient\getLocalImg')]
final class AnimeClientTest extends AnimeClientTestCase
{
	public function testArrayToToml(): void
	{
		$arr = [
			'cat' => FALSE,
			'foo' => 'bar',
			'dateTime' => (array) new DateTime(),
			'bar' => [
				'a' => 1,
				'b' => 2,
				'c' => 3,
			],
			'baz' => [
				'x' => [1, 2, 3],
				'y' => [2, 4, 6],
				'z' => [3, 6, 9],
			],
			'foobar' => [
				'z' => 3.1415926539,
				'a' => [
					'aa' => -8,
					'b' => [
						'aaa' => 4028,
						'c' => [1, 2, 3],
					],
				],
			],
		];

		$toml = arrayToToml($arr);

		$parsedArray = tomlToArray($toml);

		$this->assertEquals($arr, $parsedArray);
	}

	public function testArrayToTomlNullValue(): void
	{
		$arr = [
			'cat' => FALSE,
			'bat' => NULL,
			'foo' => 'bar',
		];

		$toml = arrayToToml($arr);
		$parsedArray = tomlToArray($toml);

		$this->assertSame([
			'cat' => FALSE,
			'foo' => 'bar',
		], $parsedArray);
	}

	public function testIsSequentialArray(): void
	{
		$this->assertFalse(isSequentialArray(0));
		$this->assertFalse(isSequentialArray([50 => 'foo']));
		$this->assertTrue(isSequentialArray([]));
		$this->assertTrue(isSequentialArray([1, 2, 3, 4, 5]));
	}

	public function testGetResponse(): void
	{
		$this->assertNotEmpty(getResponse('https://example.com'));
	}

	public function testCheckFolderPermissions(): void
	{
		$config = $this->container->get('config');
		$actual = checkFolderPermissions($config);
		$this->assertIsArray($actual);
	}

	public function testGetLocalImageEmptyUrl(): void
	{
		$actual = getLocalImg('');
		$this->assertSame('images/placeholder.webp', $actual);
	}

	public function testGetLocalImageBadUrl(): void
	{
		$actual = getLocalImg('//foo.bar');
		$this->assertSame('images/placeholder.webp', $actual);
	}

	public function testColNotEmpty(): void
	{
		$hasEmptyCols = [[
			'foo' => '',
		], [
			'foo' => '',
		]];

		$hasNonEmptyCols = [[
			'foo' => 'bar',
		], [
			'foo' => 'baz',
		]];

		$this->assertFalse(colNotEmpty($hasEmptyCols, 'foo'));
		$this->assertTrue(colNotEmpty($hasNonEmptyCols, 'foo'));
	}

	public function testClearCache(): void
	{
		$this->assertTrue(clearCache($this->container->get('cache')));
	}

	public static function getFriendlyTime(): array
	{
		$SECONDS_IN_DAY = SECONDS_IN_MINUTE * MINUTES_IN_DAY;
		$SECONDS_IN_HOUR = SECONDS_IN_MINUTE * MINUTES_IN_HOUR;
		$SECONDS_IN_YEAR = SECONDS_IN_MINUTE * MINUTES_IN_YEAR;

		return [[
			'seconds' => $SECONDS_IN_YEAR,
			'expected' => '1 year',
		], [
			'seconds' => $SECONDS_IN_HOUR,
			'expected' => '1 hour',
		], [
			'seconds' => (2 * $SECONDS_IN_YEAR) + 30,
			'expected' => '2 years, 30 seconds',
		], [
			'seconds' => (5 * $SECONDS_IN_YEAR) + (3 * $SECONDS_IN_DAY) + (17 * SECONDS_IN_MINUTE),
			'expected' => '5 years, 3 days, and 17 minutes',
		]];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('getFriendlyTime')]
	public function testGetFriendlyTime(int $seconds, string $expected): void
	{
		$actual = friendlyTime($seconds);

		$this->assertSame($expected, $actual);
	}
}
