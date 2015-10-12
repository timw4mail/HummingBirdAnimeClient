<?php

class ArrayTypeTest extends AnimeClient_TestCase {
	use Aviat\Ion\ArrayWrapper;


	public function setUp()
	{
		parent::setUp();
	}

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
	 */
	public function testCall($method, $array, $args, $expected)
	{
		$obj = $this->arr($array);
		$actual = $obj->__call($method, $args);
		$this->assertEquals($expected, $actual);
	}

	public function testMap()
	{
		$obj = $this->arr([1, 2, 3]);
		$actual = $obj->map(function($item) {
			return $item * 2;
		});

		$this->assertEquals([2, 4, 6], $actual);
	}

	public function testBadCall()
	{
		$obj = $this->arr([]);

		$this->setExpectedException('InvalidArgumentException', "Method 'foo' does not exist");
		$obj->foo();
	}

	public function testShuffle()
	{
		$original = [1, 2, 3, 4];
		$test = [1, 2, 3, 4];
		$obj = $this->arr($test);
		$actual = $obj->shuffle();

		//$this->assertNotEquals($actual, $original);
		$this->assertTrue(is_array($actual));
	}

	public function testHasKey()
	{
		$obj = $this->arr([
			'a' => 'b',
			'z' => 'y'
		]);
		$this->assertTrue($obj->has_key('a'));
		$this->assertFalse($obj->has_key('b'));
	}

	public function testHas()
	{
		$obj = $this->arr([1, 2, 6, 8, 11]);
		$this->assertTrue($obj->has(8));
		$this->assertFalse($obj->has(8745));
	}

	public function testSearch()
	{
		$obj = $this->arr([1, 2, 5, 7, 47]);
		$actual = $obj->search(47);
		$this->assertEquals(4, $actual);
	}

	public function testFill()
	{
		$obj = $this->arr([]);
		$expected = ['?', '?', '?'];
		$actual = $obj->fill(0, 3, '?');
		$this->assertEquals($actual, $expected);
	}
}