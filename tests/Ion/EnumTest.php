<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.4
 *
 * @copyright   2015 - 2025  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.3
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\Ion\Tests;

/**
 * @internal
 */
final class EnumTest extends IonTestCase
{
	public $enum;
	protected $expectedConstList = [
		'FOO' => 'bar',
		'BAR' => 'foo',
		'FOOBAR' => 'baz',
	];

	#[\Override]
	protected function setUp(): void
	{
		parent::setUp();
		$this->enum = new TestConstList();
	}

	public function testStaticGetConstList()
	{
		$actual = TestConstList::getConstList();
		$this->assertSame($this->expectedConstList, $actual);
	}

	public function testGetConstList()
	{
		$actual = $this->enum->getConstList();
		$this->assertSame($this->expectedConstList, $actual);
	}

	public static function dataIsValid()
	{
		return [
			'Valid' => [
				'value' => 'baz',
				'expected' => true,
				'static' => false,
			],
			'ValidStatic' => [
				'value' => 'baz',
				'expected' => true,
				'static' => true,
			],
			'Invalid' => [
				'value' => 'foobar',
				'expected' => false,
				'static' => false,
			],
			'InvalidStatic' => [
				'value' => 'foobar',
				'expected' => false,
				'static' => true,
			],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('dataIsValid')]
	public function testIsValid(mixed $value, mixed $expected, mixed $static)
	{
		$actual = $static
			? TestConstList::isValid($value)
			: $this->enum->isValid($value);

		$this->assertSame($expected, $actual);
	}
}
