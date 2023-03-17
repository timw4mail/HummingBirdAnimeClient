<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @copyright   2015 - 2022  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshome.page/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests\Types;

use Aviat\AnimeClient\Types\Config;

/**
 * @internal
 */
final class ConfigTest extends ConfigTestCase
{
	protected function setUp(): void
	{
		parent::setUp();

		$this->testClass = Config::class;
	}

	public function testSetMethods(): void
	{
		$type = $this->testClass::from([
			'anilist' => [],
			'cache' => [],
			'database' => [],
		]);

		$this->assertSame(3, $type->count());
	}

	public function testOffsetUnset(): void
	{
		$type = $this->testClass::from([
			'anilist' => [],
		]);

		$this->assertTrue($type->offsetExists('anilist'));

		$type->offsetUnset('anilist');

		$this->assertNotTrue($type->offsetExists('anilist'));
	}
}
