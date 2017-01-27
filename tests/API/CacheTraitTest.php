<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\API;

use Aviat\AnimeClient\API\CacheTrait;

class CacheTraitTest extends \AnimeClient_TestCase {
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