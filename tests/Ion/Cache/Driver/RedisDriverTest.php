<?php

require_once('CacheDriverBase.php');

use Aviat\Ion\Friend;
use Aviat\Ion\Cache\Driver\RedisDriver;

class CacheRedisDriverTest extends AnimeClient_TestCase {
	use CacheDriverBase;
	
	protected $driver;
	
	public function setUp()
	{
		parent::setUp();
		$this->driver = new RedisDriver($this->container);
	}
}