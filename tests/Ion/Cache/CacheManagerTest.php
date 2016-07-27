<?php

use Aviat\Ion\Friend;
use Aviat\Ion\Cache\CacheManager;

class CacheManagerText extends AnimeClient_TestCase {

	protected $cachedTime;

	public function __call($name, $args)
	{
		return call_user_func_array($name, $args);
	}

	public function setUp()
	{
		parent::setUp();
		$this->cache = new CacheManager($this->container->get('config'), $this->container);
		$this->friend = new Friend($this->cache);
	}

	public function testGet()
	{
		$this->cachedTime = $this->cache->get($this, 'time');
		$this->assertEquals($this->cache->get($this, 'time'), $this->cachedTime);
	}

	public function testGetFresh()
	{
		$this->assertNotEquals($this->cache->getFresh($this, 'time'), $this->cachedTime);
	}

	public function testPurge()
	{
		$this->cachedTime = $this->cache->get($this, 'time');
		$key = $this->friend->generateHashForMethod($this, 'time', []);
		$this->cache->purge();
		$this->assertEmpty($this->friend->driver->get($key));
	}
}