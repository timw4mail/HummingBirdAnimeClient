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
 * @copyright   2015 - 2021  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\Ion\Tests;

use Aviat\Ion\Enum;

class EnumTest extends IonTestCase {

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