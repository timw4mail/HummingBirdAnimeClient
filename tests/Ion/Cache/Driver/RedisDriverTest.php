<?php

require_once('CacheDriverBase.php');

use Aviat\Ion\Cache\Driver\RedisDriver;

class CacheRedisDriverTest extends AnimeClient_TestCase {
	use CacheDriverBase;
	
	protected $driver;
	
	public function setUp()
	{
		parent::setUp();

		$this->driver = new RedisDriver($this->container);
	}

	public function tearDown()
	{
		parent::tearDown();

		if ( ! is_null($this->driver))
		{
			$this->driver->__destruct();
		}

	}
}