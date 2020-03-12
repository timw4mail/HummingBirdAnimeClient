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

namespace Aviat\Ion\Tests;

use Aviat\Ion\Enum;

class EnumTest extends Ion_TestCase {

	protected $expectedConstList = [
		'FOO' => 'bar',
		'BAR' => 'foo',
		'FOOBAR' => 'baz'
	];

	public function setUp(): void	{
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