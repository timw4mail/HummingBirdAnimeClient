<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.4
 *
 * @copyright   2015 - 2026  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.3
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

	#[\Override]
	protected function setUp(): void
	{
		parent::setUp();
		$this->testClass = new class() {
			use CacheTrait;
		};
	}

	public function testSetGet(): void
	{
		$cachePool = $this->container->get('cache');
		$this->testClass->setCache($cachePool);
		$this->assertSame($cachePool, $this->testClass->getCache());
	}

	public function testGetCached(): void
	{
		$cachePool = $this->container->get('cache');
		$cachePool->clear();
		$this->testClass->setCache($cachePool);

		$key = 'test-key';
		$value = 'test-value';
		$primer = fn () => $value;

		// First call, should call primer and set cache
		$result = $this->testClass->getCached($key, $primer);
		$this->assertEquals($value, $result);
		$this->assertTrue($cachePool->has($key));
		$this->assertEquals($value, $cachePool->get($key));

		// Second call, should return from cache
		$called = false;
		$primer2 = function () use (&$called) {
			$called = true;

			return 'wrong-value';
		};

		$result2 = $this->testClass->getCached($key, $primer2);
		$this->assertEquals($value, $result2);
		$this->assertFalse($called);
	}

	public function testGetCachedWithArgs(): void
	{
		$cachePool = $this->container->get('cache');
		$cachePool->clear();
		$this->testClass->setCache($cachePool);

		$key = 'arg-key';
		$primer = fn ($arg) => "value-{$arg}";

		$result = $this->testClass->getCached($key, $primer, ['foo']);
		$this->assertEquals('value-foo', $result);
	}
}
