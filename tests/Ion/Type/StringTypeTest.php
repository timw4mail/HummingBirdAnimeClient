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

namespace Aviat\Ion\Tests\Type;

use Aviat\Ion\Tests\IonTestCase;
use Aviat\Ion\Type\StringType;
use Aviat\Ion\Type\Stringy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\IgnoreClassForCodeCoverage;
use PHPUnit\Framework\Attributes\Test;

namespace Aviat\Ion\Tests\Type;

use Aviat\Ion\Tests\IonTestCase;
use Aviat\Ion\Type\StringType;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * @internal
 */
final class StringTypeTest extends IonTestCase
{
	public function testAppend(): void
	{
		$str = StringType::from('foo');
		$this->assertEquals('foobar', (string) $str->append('bar'));
	}

	public function testAt(): void
	{
		$str = StringType::from('foo');
		$this->assertEquals('f', (string) $str->at(0));
		$this->assertEquals('o', (string) $str->at(1));
	}

	public function testBetween(): void
	{
		$str = StringType::from('foo[bar]baz');
		$this->assertEquals('bar', (string) $str->between('[', ']'));
	}

	public function testContainsAllAny(): void
	{
		$str = StringType::from('foo bar baz');
		$this->assertTrue($str->containsAll(['foo', 'baz']));
		$this->assertFalse($str->containsAll(['foo', 'qux']));
		$this->assertTrue($str->containsAny(['qux', 'bar']));
		$this->assertFalse($str->containsAny(['qux', 'quux']));
	}

	public function testCountSubstr(): void
	{
		$str = StringType::from('foo bar foo baz');
		$this->assertEquals(2, $str->countSubstr('foo'));
		$this->assertEquals(1, $str->countSubstr('bar'));
	}

	public function testEndsWithAny(): void
	{
		$str = StringType::from('foo bar baz');
		$this->assertTrue($str->endsWithAny(['baz', 'qux']));
		$this->assertFalse($str->endsWithAny(['foo', 'bar']));
	}

	public function testCamelize(): void
	{
		$str = StringType::from('foo_bar_baz');
		$this->assertEquals('fooBarBaz', (string) $str->camelize());
	}

	public function testContains(): void
	{
		$str = StringType::from('foo bar baz');
		$this->assertTrue($str->contains('bar'));
		$this->assertFalse($str->contains('qux'));
		$this->assertTrue($str->contains('BAR', false));
	}

	public function testDasherize(): void
	{
		$str = StringType::from('fooBarBaz');
		$this->assertEquals('foo-bar-baz', (string) $str->dasherize());
	}

	public function testEndsWith(): void
	{
		$str = StringType::from('foo bar baz');
		$this->assertTrue($str->endsWith('baz'));
		$this->assertFalse($str->endsWith('bar'));
	}

	public function testEnsureLeft(): void
	{
		$str = StringType::from('bar');
		$this->assertEquals('foobar', (string) $str->ensureLeft('foo'));
		$str2 = StringType::from('foobar');
		$this->assertEquals('foobar', (string) $str2->ensureLeft('foo'));
	}

	public function testEnsureRight(): void
	{
		$str = StringType::from('foo');
		$this->assertEquals('foobar', (string) $str->ensureRight('bar'));
		$str2 = StringType::from('foobar');
		$this->assertEquals('foobar', (string) $str2->ensureRight('bar'));
	}

	public function testFirst(): void
	{
		$str = StringType::from('foobar');
		$this->assertEquals('foo', (string) $str->first(3));
	}

	public function testHtmlDecodeEncode(): void
	{
		$str = StringType::from('& < > " \'');
		$encoded = $str->htmlEncode();
		$this->assertEquals('&amp; &lt; &gt; &quot; \'', (string) $encoded);
		$this->assertEquals('& < > " \'', (string) $encoded->htmlDecode());
	}

	public function testHumanize(): void
	{
		$str = StringType::from('author_id');
		$this->assertEquals('Author', (string) $str->humanize());
	}

	public function testIndexOf(): void
	{
		$str = StringType::from('foobar');
		$this->assertEquals(3, $str->indexOf('bar'));
		$this->assertFalse($str->indexOf('qux'));
	}

	public function testIsAlpha(): void
	{
		$this->assertTrue(StringType::from('foo')->isAlpha());
		$this->assertFalse(StringType::from('foo123')->isAlpha());
	}

	public function testIsAlphanumeric(): void
	{
		$this->assertTrue(StringType::from('foo123')->isAlphanumeric());
		$this->assertFalse(StringType::from('foo 123')->isAlphanumeric());
	}

	public function testIsBlank(): void
	{
		$this->assertTrue(StringType::from('  ')->isBlank());
		$this->assertFalse(StringType::from('foo')->isBlank());
	}

	public function testIsJson(): void
	{
		$this->assertTrue(StringType::from('{"foo":"bar"}')->isJson());
		$this->assertFalse(StringType::from('foo')->isJson());
	}

	public function testIsLowerCase(): void
	{
		$this->assertTrue(StringType::from('foo')->isLowerCase());
		$this->assertFalse(StringType::from('Foo')->isLowerCase());
	}

	public function testLast(): void
	{
		$str = StringType::from('foobar');
		$this->assertEquals('bar', (string) $str->last(3));
	}

	public function testLength(): void
	{
		$this->assertEquals(6, StringType::from('foobar')->length());
		$this->assertEquals(2, StringType::from('忍者')->length());
	}

	public function testLowerCaseFirst(): void
	{
		$this->assertEquals('fooBar', (string) StringType::from('FooBar')->lowerCaseFirst());
	}

	public function testPad(): void
	{
		$str = StringType::from('foo');
		$this->assertEquals('foo  ', (string) $str->pad(5));
		$this->assertEquals('  foo', (string) $str->pad(5, ' ', 'left'));
		$this->assertEquals(' foo ', (string) $str->pad(5, ' ', 'both'));
	}

	public function testPrepend(): void
	{
		$this->assertEquals('foobar', (string) StringType::from('bar')->prepend('foo'));
	}

	public function testRegexReplace(): void
	{
		$str = StringType::from('foo bar baz');
		$this->assertEquals('foo-bar-baz', (string) $str->regexReplace(' ', '-'));
	}

	public function testRemoveLeftRight(): void
	{
		$str = StringType::from('foobar');
		$this->assertEquals('bar', (string) $str->removeLeft('foo'));
		$this->assertEquals('foo', (string) $str->removeRight('bar'));
	}

	public function testRepeat(): void
	{
		$this->assertEquals('foofoo', (string) StringType::from('foo')->repeat(2));
	}

	public function testReplace(): void
	{
		$this->assertEquals('foo-bar-baz', (string) StringType::from('foo bar baz')->replace(' ', '-'));
	}

	public function testReverse(): void
	{
		$this->assertEquals('raboof', (string) StringType::from('foobar')->reverse());
	}

	public function testSafeTruncate(): void
	{
		$str = StringType::from('foo bar baz');
		$this->assertEquals('foo bar...', (string) $str->safeTruncate(10, '...'));
	}

	public function testShuffle(): void
	{
		$str = StringType::from('abcdef');
		$shuffled = $str->shuffle();
		$this->assertEquals(6, $shuffled->length());
		$this->assertNotEquals('abcdef', (string) $shuffled);
	}

	public function testSlugify(): void
	{
		$str = StringType::from('Foo Bar Baz!!!');
		$this->assertEquals('foo-bar-baz', (string) $str->slugify());
	}

	public function testSlice(): void
	{
		$str = StringType::from('foobar');
		$this->assertEquals('ooba', (string) $str->slice(1, 5));
		$this->assertEquals('bar', (string) $str->slice(3));
	}

	public function testSplit(): void
	{
		$str = StringType::from('foo,bar,baz');
		$parts = $str->split(',');
		$this->assertCount(3, $parts);
		$this->assertEquals('foo', (string) $parts[0]);
	}

	public function testStripWhitespace(): void
	{
		$this->assertEquals('foobarbaz', (string) StringType::from('foo bar baz')->stripWhitespace());
	}

	public function testSubstr(): void
	{
		$this->assertEquals('bar', (string) StringType::from('foobar')->substr(3));
	}

	public function testSurround(): void
	{
		$this->assertEquals('[]foo[]', (string) StringType::from('foo')->surround('[]'));
		$this->assertEquals('|foo|', (string) StringType::from('foo')->surround('|'));
	}

	public function testSwapCase(): void
	{
		$this->assertEquals('fOObAR', (string) StringType::from('FooBar')->swapCase());
	}

	public function testTidy(): void
	{
		// Smart quotes etc
		$str = StringType::from("\u{201C}foo\u{201D}");
		$this->assertEquals('"foo"', (string) $str->tidy());
	}

	public function testTitleize(): void
	{
		$this->assertEquals('Foo Bar Baz', (string) StringType::from('foo bar baz')->titleize());
	}

	public function testToAscii(): void
	{
		$this->assertEquals('aeoeue', (string) StringType::from('äöü')->toAscii('de'));
	}

	public function testToBoolean(): void
	{
		$this->assertTrue(StringType::from('true')->toBoolean());
		$this->assertTrue(StringType::from('1')->toBoolean());
		$this->assertTrue(StringType::from('on')->toBoolean());
		$this->assertTrue(StringType::from('yes')->toBoolean());
		$this->assertFalse(StringType::from('false')->toBoolean());
		$this->assertFalse(StringType::from('0')->toBoolean());
	}

	public function testToLowerCaseUpperCase(): void
	{
		$this->assertEquals('foo', (string) StringType::from('FOO')->toLowerCase());
		$this->assertEquals('FOO', (string) StringType::from('foo')->toUpperCase());
	}

	public function testToSpacesTabs(): void
	{
		$this->assertEquals('    foo', (string) StringType::from("\tfoo")->toSpaces());
		$this->assertEquals("\tfoo", (string) StringType::from('    foo')->toTabs());
	}

	public function testTrim(): void
	{
		$this->assertEquals('foo', (string) StringType::from('  foo  ')->trim());
		$this->assertEquals('foo  ', (string) StringType::from('  foo  ')->trimLeft());
		$this->assertEquals('  foo', (string) StringType::from('  foo  ')->trimRight());
	}

	public function testTruncate(): void
	{
		$this->assertEquals('foo...', (string) StringType::from('foobarbaz')->truncate(6, '...'));
	}

	public function testUnderscored(): void
	{
		$this->assertEquals('foo_bar_baz', (string) StringType::from('fooBarBaz')->underscored());
	}

	public function testUpperCamelize(): void
	{
		$this->assertEquals('FooBarBaz', (string) StringType::from('foo_bar_baz')->upperCamelize());
	}

	public function testUpperCaseFirst(): void
	{
		$this->assertEquals('FooBar', (string) StringType::from('fooBar')->upperCaseFirst());
	}

	public function testChars(): void
	{
		$this->assertEquals(['f', 'o', 'o'], StringType::from('foo')->chars());
	}

	public function testCollapseWhitespace(): void
	{
		$this->assertEquals(
			'foo bar baz',
			(string) StringType::from('foo  bar   baz')->collapseWhitespace(),
		);
	}

	public function testDelimit(): void
	{
		$this->assertEquals('foo*bar', (string) StringType::from('fooBar')->delimit('*'));
	}

	public function testHasCases(): void
	{
		$this->assertTrue(StringType::from('foo')->hasLowerCase());
		$this->assertFalse(StringType::from('FOO')->hasLowerCase());
		$this->assertTrue(StringType::from('FOO')->hasUpperCase());
		$this->assertFalse(StringType::from('foo')->hasUpperCase());
	}

	public function testIndexOfLast(): void
	{
		$this->assertEquals(6, StringType::from('foobarfoo')->indexOfLast('foo'));
	}

	public function testInsert(): void
	{
		$this->assertEquals('foobar', (string) StringType::from('foo')->insert('bar', 3));
	}

	public function testIsChecks(): void
	{
		$this->assertTrue(StringType::from('Zm9v')->isBase64());
		$this->assertTrue(StringType::from('abcdef0123')->isHexadecimal());
		$this->assertTrue(StringType::from('s:3:"foo";')->isSerialized());
		$this->assertTrue(StringType::from('FOO')->isUpperCase());
	}

	public function testLines(): void
	{
		$this->assertCount(2, StringType::from("foo\nbar")->lines());
	}

	public function testLongestCommon(): void
	{
		$this->assertEquals('fooba', (string) StringType::from('foobar')->longestCommonPrefix('foobaz'));
		$this->assertEquals('bar', (string) StringType::from('foobar')->longestCommonSubstring('quxbar'));
		$this->assertEquals('bar', (string) StringType::from('foobar')->longestCommonSuffix('quxbar'));
	}

	public function testStartsWith(): void
	{
		$this->assertTrue(StringType::from('foobar')->startsWith('foo'));
		$this->assertTrue(StringType::from('foobar')->startsWithAny(['foo', 'qux']));
	}

	public function testToTitleCase(): void
	{
		$this->assertEquals('Foo Bar', (string) StringType::from('foo bar')->toTitleCase());
	}

	public function testArrayAccess(): void
	{
		$str = StringType::from('foo');
		$this->assertTrue(isset($str[0]));
		$this->assertEquals('f', $str[0]);
		$this->assertFalse(isset($str[3]));
	}

	public function testCountable(): void
	{
		$this->assertCount(3, StringType::from('foo'));
	}

	public function testGetEncoding(): void
	{
		$this->assertEquals('UTF-8', StringType::from('foo')->getEncoding());
	}

	public function testGetIterator(): void
	{
		$str = StringType::from('foo');
		$chars = [];
		foreach ($str as $char)
		{
			$chars[] = $char;
		}

		$this->assertEquals(['f', 'o', 'o'], $chars);
	}

	public static function dataFuzzyCaseMatch(): array
	{
		return [
			'space separated' => [
				'str1' => 'foo bar baz',
				'str2' => 'foo-bar-baz',
				'expected' => true,
			],
			'camelCase' => [
				'str1' => 'fooBarBaz',
				'str2' => 'foo-bar-baz',
				'expected' => true,
			],
			'PascalCase' => [
				'str1' => 'FooBarBaz',
				'str2' => 'foo-bar-baz',
				'expected' => true,
			],
			'snake_case' => [
				'str1' => 'foo_bar_baz',
				'str2' => 'foo-bar-baz',
				'expected' => true,
			],
			'mEsSYcAse' => [
				'str1' => 'fOObArBAZ',
				'str2' => 'foo-bar-baz',
				'expected' => false,
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
