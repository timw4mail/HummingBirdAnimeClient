<?php

class ArrayTypeTest extends AnimeClient_TestCase {
	use Aviat\Ion\ArrayWrapper;


	public function setUp()
	{
		parent::setUp();
	}

	public function testMerge()
	{
		$obj = $this->arr([1, 3, 5, 7]);
		$even_array = [2, 4, 6, 8];
		$expected = [1, 3, 5, 7, 2, 4, 6, 8];

		$actual = $obj->merge($even_array);
		$this->assertEquals($expected, $actual);
	}

	public function testShuffle()
	{
		$original = [1, 2, 3, 4];
		$test = [1, 2, 3, 4];
		$obj = $this->arr($test);
		$actual = $obj->shuffle();

		$this->assertNotEquals($actual, $original);
		$this->assertTrue(is_array($actual));
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