<?php

use Aviat\Ion\Enum;

class EnumTest extends AnimeClient_TestCase {

	protected $expectedConstList = [
		'FOO' => 'bar',
		'BAR' => 'foo',
		'FOOBAR' => 'baz'
	];

	public function setUp()
	{
		parent::setUp();
		$this->enum = new TestEnum();
	}

	public function testStaticGetConstList()
	{
		$actual = TestEnum::getConstList();
		$this->assertEquals($this->expectedConstList, $actual);
	}

	public function testGetConstList()
	{
		$actual = $this->enum->getConstList();
		$this->assertEquals($this->expectedConstList, $actual);
	}

	public function dataIsValid()
	{
		return [
			'Valid' => [
				'value' => 'baz',
				'expected' => TRUE,
				'static' => FALSE
			],
			'ValidStatic' => [
				'value' => 'baz',
				'expected' => TRUE,
				'static' => TRUE
			],
			'Invalid' => [
				'value' => 'foobar',
				'expected' => FALSE,
				'static' => FALSE
			],
			'InvalidStatic' => [
				'value' => 'foobar',
				'expected' => FALSE,
				'static' => TRUE
			]
		];
	}

	/**
	 * @dataProvider dataIsValid
	 */
	public function testIsValid($value, $expected, $static)
	{
		$actual = ($static)
			? TestEnum::isValid($value)
			: $this->enum->isValid($value);

		$this->assertEquals($expected, $actual);
	}
}