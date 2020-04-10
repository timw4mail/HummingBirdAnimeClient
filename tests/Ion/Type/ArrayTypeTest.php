<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\Ion\Tests\Type;

use Aviat\Ion\Type\ArrayType;
use Aviat\Ion\Tests\IonTestCase;

class ArrayTypeTest extends IonTestCase {
	public function dataCall()
	{
		$method_map = [
			'chunk' => 'array_chunk',
			'pluck' => 'array_column',
			'assoc_diff' => 'array_diff_assoc',
			'key_diff' => 'array_diff_key',
			'diff' => 'array_diff',
			'filter' => 'array_filter',
			'flip' => 'array_flip',
			'intersect' => 'array_intersect',
			'keys' => 'array_keys',
			'merge' => 'array_merge',
			'pad' => 'array_pad',
			'random' => 'array_rand',
			'reduce' => 'array_reduce',
		];

		return [
			'array_merge' => [
				'method' => 'merge',
				'array' => [1, 3, 5, 7],
				'args' => [[2, 4, 6, 8]],
				'expected' => [1, 3, 5, 7, 2, 4, 6, 8]
			],
			'array_product' => [
				'method' => 'product',
				'array' => [1, 2, 3],
				'args' => [],
				'expected' => 6
			],
			'array_reverse' => [
				'method' => 'reverse',
				'array' => [1, 2, 3, 4, 5],
				'args' => [],
				'expected' => [5, 4, 3, 2, 1]
			],
			'array_sum' => [
				'method' => 'sum',
				'array' => [1, 2, 3, 4, 5, 6],
				'args' => [],
				'expected' => 21
			],
			'array_unique' => [
				'method' => 'unique',
				'array' => [1, 1, 3, 2, 2, 2, 3, 3, 5],
				'args' => [SORT_REGULAR],
				'expected' => [0 => 1, 2 => 3, 3 => 2, 8 => 5]
			],
			'array_values' => [
				'method' => 'values',
				'array' => ['foo' => 'bar', 'baz' => 'foobar'],
				'args' => [],
				'expected' => ['bar', 'foobar']
			]
		];
	}

	/**
	 * Test the array methods defined for the __Call method
	 *
	 * @dataProvider dataCall
	 * @param string $method
	 * @param array $array
	 * @param array $args
	 * @param $expected
	 */
	public function testCall(string $method, array $array, array $args, $expected): void
	{
		$obj = ArrayType::from($array);
		$actual = $obj->__call($method, $args);
		$this->assertEquals($expected, $actual);
	}

	public function testSet(): void
	{
		$obj = ArrayType::from([]);
		$arraytype = $obj->set('foo', 'bar');

		$this->assertInstanceOf(ArrayType::class, $arraytype);
		$this->assertEquals('bar', $obj->get('foo'));
	}

	public function testGet(): void
	{
		$array = [1, 2, 3, 4, 5];
		$obj = ArrayType::from($array);
		$this->assertEquals($array, $obj->get());
		$this->assertEquals(1, $obj->get(0));
		$this->assertEquals(5, $obj->get(4));
	}

	public function testGetDeepKey(): void
	{
		$arr = [
			'foo' => 'bar',
			'baz' => [
				'bar' => 'foobar'
			]
		];
		$obj = ArrayType::from($arr);
		$this->assertEquals('foobar', $obj->getDeepKey(['baz', 'bar']));
		$this->assertNull($obj->getDeepKey(['foo', 'bar', 'baz']));
	}

	public function testMap(): void
	{
		$obj = ArrayType::from([1, 2, 3]);
		$actual = $obj->map(function($item) {
			return $item * 2;
		});

		$this->assertEquals([2, 4, 6], $actual);
	}

	public function testBadCall(): void
	{
		$obj = ArrayType::from([]);

		$this->expectException('InvalidArgumentException');
		$this->expectExceptionMessage("Method 'foo' does not exist");

		$obj->foo();
	}

	public function testShuffle(): void
	{
		$original = [1, 2, 3, 4];
		$test = [1, 2, 3, 4];
		$obj = ArrayType::from($test);
		$actual = $obj->shuffle();

		//$this->assertNotEquals($actual, $original);
		$this->assertTrue(is_array($actual));
	}

	public function testHasKey(): void
	{
		$obj = ArrayType::from([
			'a' => 'b',
			'z' => 'y'
		]);
		$this->assertTrue($obj->hasKey('a'));
		$this->assertFalse($obj->hasKey('b'));
	}

	public function testHasKeyArray(): void
	{
		$obj = ArrayType::from([
			'foo' => [
				'bar' => [
					'baz' => [
						'foobar' => NULL,
						'one' => 1,
					],
				],
			],
		]);

		$this->assertTrue($obj->hasKey(['foo']));
		$this->assertTrue($obj->hasKey(['foo', 'bar']));
		$this->assertTrue($obj->hasKey(['foo', 'bar', 'baz']));
		$this->assertTrue($obj->hasKey(['foo', 'bar', 'baz', 'one']));
		$this->assertTrue($obj->hasKey(['foo', 'bar', 'baz', 'foobar']));

		$this->assertFalse($obj->hasKey(['foo', 'baz']));
		$this->assertFalse($obj->hasKey(['bar', 'baz']));
	}

	public function testHas(): void
	{
		$obj = ArrayType::from([1, 2, 6, 8, 11]);
		$this->assertTrue($obj->has(8));
		$this->assertFalse($obj->has(8745));
	}

	public function testSearch(): void
	{
		$obj = ArrayType::from([1, 2, 5, 7, 47]);
		$actual = $obj->search(47);
		$this->assertEquals(4, $actual);
	}

	public function testFill(): void
	{
		$obj = ArrayType::from([]);
		$expected = ['?', '?', '?'];
		$actual = $obj->fill(0, 3, '?');
		$this->assertEquals($actual, $expected);
	}
}