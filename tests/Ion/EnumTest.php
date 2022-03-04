<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshome.page>
 * @copyright   2015 - 2022  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\Ion\Tests;

/**
 * @internal
 */
final class EnumTest extends IonTestCase
{
	protected $expectedConstList = [
		'FOO' => 'bar',
		'BAR' => 'foo',
		'FOOBAR' => 'baz',
	];

	protected function setUp(): void
	{
		parent::setUp();
		$this->enum = new TestEnum();
	}

	public function testStaticGetConstList()
	{
		$actual = TestEnum::getConstList();
		$this->assertSame($this->expectedConstList, $actual);
	}

	public function testGetConstList()
	{
		$actual = $this->enum->getConstList();
		$this->assertSame($this->expectedConstList, $actual);
	}

	public function dataIsValid()
	{
		return [
			'Valid' => [
				'value' => 'baz',
				'expected' => TRUE,
				'static' => FALSE,
			],
			'ValidStatic' => [
				'value' => 'baz',
				'expected' => TRUE,
				'static' => TRUE,
			],
			'Invalid' => [
				'value' => 'foobar',
				'expected' => FALSE,
				'static' => FALSE,
			],
			'InvalidStatic' => [
				'value' => 'foobar',
				'expected' => FALSE,
				'static' => TRUE,
			],
		];
	}

	/**
	 * @dataProvider dataIsValid
	 * @param mixed $value
	 * @param mixed $expected
	 * @param mixed $static
	 */
	public function testIsValid($value, $expected, $static)
	{
		$actual = ($static)
			? TestEnum::isValid($value)
			: $this->enum->isValid($value);

		$this->assertSame($expected, $actual);
	}
}
