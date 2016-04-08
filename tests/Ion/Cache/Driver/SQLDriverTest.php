<?php

require_once('CacheDriverBase.php');

use Aviat\Ion\Friend;
use Aviat\Ion\Cache\Driver\SQLDriver;

class CacheSQLDriverTest extends AnimeClient_TestCase {
	use CacheDriverBase;
	
	protected $driver;
	
	public function setUp()
	{
		parent::setUp();
		$this->driver = new SQLDriver($this->container);
		$friend = new Friend($this->driver);
		$friend->db->query('CREATE TABLE IF NOT EXISTS "cache" ("key" TEXT NULL, "value" TEXT NULL, PRIMARY KEY ("key"))');
	}
}