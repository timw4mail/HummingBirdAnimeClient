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

namespace Aviat\Ion\Tests\Type;

use Aviat\Ion\Tests\IonTestCase;
use Aviat\Ion\Type\StringType;

/**
 * @internal
 */
final class StringTypeTest extends IonTestCase
{
	public function dataFuzzyCaseMatch(): array
	{
		return [
			'space separated' => [
				'str1' => 'foo bar baz',
				'str2' => 'foo-bar-baz',
				'expected' => TRUE,
			],
			'camelCase' => [
				'str1' => 'fooBarBaz',
				'str2' => 'foo-bar-baz',
				'expected' => TRUE,
			],
			'PascalCase' => [
				'str1' => 'FooBarBaz',
				'str2' => 'foo-bar-baz',
				'expected' => TRUE,
			],
			'snake_case' => [
				'str1' => 'foo_bar_baz',
				'str2' => 'foo-bar-baz',
				'expected' => TRUE,
			],
			'mEsSYcAse' => [
				'str1' => 'fOObArBAZ',
				'str2' => 'foo-bar-baz',
				'expected' => FALSE,
			],
		];
	}

	/**
	 * @dataProvider dataFuzzyCaseMatch
	 */
	public function testFuzzyCaseMatch(string $str1, string $str2, bool $expected): void
	{
		$actual = StringType::from($str1)->fuzzyCaseMatch($str2);
		$this->assertSame($expected, $actual);
	}
}
