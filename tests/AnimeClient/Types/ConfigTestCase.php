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

namespace Aviat\AnimeClient\Tests\Types;

use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use Aviat\AnimeClient\Types\UndefinedPropertyException;

abstract class ConfigTestCase extends AnimeClientTestCase
{
	public string $testClass;

	public function testCheck(): void
	{
		$result = $this->testClass::check([]);
		$this->assertSame([], $result);
	}

	public function testSetUndefinedProperty(): void
	{
		$this->expectException(UndefinedPropertyException::class);
		$this->testClass::from([
			'foobar' => 'baz',
		]);
	}

	public function testToString(): void
	{
		$actual = $this->testClass::from([])->__toString();
		$this->assertMatchesSnapshot($actual);
	}

	public function testOffsetExists(): void
	{
		$actual = $this->testClass::from([
			'anilist' => [],
		])->offsetExists('anilist');
		$this->assertTrue($actual);
	}

	public function testSetState(): void
	{
		$normal = $this->testClass::from([]);
		$setState = $this->testClass::__set_state([]);

		$this->assertEquals($normal, $setState);
	}

	public function testIsEmpty(): void
	{
		$type = $this->testClass::from([]);
		$this->assertTrue($type->isEmpty());
	}

	public function testCount(): void
	{
		$type = $this->testClass::from([]);
		$this->assertSame(0, $type->count());
	}
}
