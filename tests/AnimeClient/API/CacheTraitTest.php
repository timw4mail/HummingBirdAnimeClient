<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.1
 *
 * @copyright   2015 - 2023  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests\API;

use Aviat\AnimeClient\API\CacheTrait;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;

/**
 * @internal
 */
final class CacheTraitTest extends AnimeClientTestCase
{
	protected $testClass;

	protected function setUp(): void
	{
		parent::setUp();
		$this->testClass = new class () {
			use CacheTrait;
		};
	}

	public function testSetGet()
	{
		$cachePool = $this->container->get('cache');
		$this->testClass->setCache($cachePool);
		$this->assertSame($cachePool, $this->testClass->getCache());
	}
}
