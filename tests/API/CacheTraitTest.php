<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2017  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests\API;

use Aviat\AnimeClient\API\CacheTrait;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;

class CacheTraitTest extends AnimeClientTestCase {
	public function setUp()
	{
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