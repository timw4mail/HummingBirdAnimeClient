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

namespace Aviat\Ion\Tests\Type;

use Aviat\Ion\Tests\IonTestCase;
use Aviat\Ion\Type\{StringType, Stringy};
use PHPUnit\Framework\Attributes\{DataProvider, IgnoreClassForCodeCoverage, Test};

/**
 * @internal
 */
#[IgnoreClassForCodeCoverage(Stringy::class)]
final class StringTypeTest extends IonTestCase
{
	public static function dataFuzzyCaseMatch(): array
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

	#[DataProvider('dataFuzzyCaseMatch')]
	#[Test]
	public function fuzzyCaseMatch(string $str1, string $str2, bool $expected): void
	{
		$actual = StringType::from($str1)->fuzzyCaseMatch($str2);
		$this->assertSame($expected, $actual);
	}
}
