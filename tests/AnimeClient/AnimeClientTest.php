<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4+
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests;

use function Aviat\AnimeClient\arrayToToml;
use function Aviat\AnimeClient\isSequentialArray;
use function Aviat\AnimeClient\tomlToArray;

class AnimeClientTest extends AnimeClientTestCase
{
	public function testArrayToToml ()
	{
		$arr = [
			'cat' => false,
			'foo' => 'bar',
			'dateTime' => (array) new \DateTime(),
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
				'z' => 22/7,
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

	public function testIsSequentialArray()
	{
		$this->assertFalse(isSequentialArray(0));
		$this->assertFalse(isSequentialArray([50 => 'foo']));
		$this->assertTrue(isSequentialArray([]));
		$this->assertTrue(isSequentialArray([1,2,3,4,5]));
	}
}