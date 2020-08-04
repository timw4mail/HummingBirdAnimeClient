<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.1
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests\API;

use Aviat\AnimeClient\API\CacheTrait;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;

class CacheTraitTest extends AnimeClientTestCase {

	protected $testClass;

	public function setUp(): void	{
		parent::setUp();
		$this->testClass = new class {
			use CacheTrait;
		};
	}

	public function testSetGet()
	{
		$cachePool = $this->container->get('cache');
		$this->testClass->setCache($cachePool);
		$this->assertEquals($cachePool, $this->testClass->getCache());
	}

	public function testGetHashForMethodCall()
	{
		$hash = $this->testClass->getHashForMethodCall($this, __METHOD__, []);
		$this->assertEquals('684ba0a5c29ffec452c5f6a07d2eee6932575490', $hash);
	}
}