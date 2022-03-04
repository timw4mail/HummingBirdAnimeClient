<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2021  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests;

use DateTime;
use function Aviat\AnimeClient\{arrayToToml, checkFolderPermissions, clearCache, colNotEmpty, getLocalImg, getResponse, isSequentialArray, tomlToArray};

/**
 * @internal
 */
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
				'z' => 22 / 7,
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
}
