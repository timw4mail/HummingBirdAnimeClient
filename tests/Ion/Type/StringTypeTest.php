<?php declare(strict_types=1);
/**
 * Ion
 *
 * Building blocks for web development
 *
 * PHP version 7.2
 *
 * @package     Ion
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2019 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     3.0.0
 * @link        https://git.timshomepage.net/aviat/ion
 */

namespace Aviat\Ion\Tests\Type;

use Aviat\Ion\StringWrapper;
use Aviat\Ion\Tests\Ion_TestCase;

class StringTypeTest extends Ion_TestCase {
	use StringWrapper;


	public function dataFuzzyCaseMatch()
	{
		return [
			'space separated' => [
				'str1' => 'foo bar baz',
				'str2' => 'foo-bar-baz',
				'expected' => true
			],
			'camelCase' => [
				'str1' => 'fooBarBaz',
				'str2' => 'foo-bar-baz',
				'expected' => true
			],
			'PascalCase' => [
				'str1' => 'FooBarBaz',
				'str2' => 'foo-bar-baz',
				'expected' => true
			],
			'snake_case' => [
				'str1' => 'foo_bar_baz',
				'str2' => 'foo-bar-baz',
				'expected' => true
			],
			'mEsSYcAse' => [
				'str1' => 'fOObArBAZ',
				'str2' => 'foo-bar-baz',
				'expected' => false
			],
		];
	}

	/**
	 * @dataProvider dataFuzzyCaseMatch
	 */
	public function testFuzzyCaseMatch($str1, $str2, $expected)
	{
		$actual = $this->string($str1)->fuzzyCaseMatch($str2);
		$this->assertEquals($expected, $actual);
	}

}