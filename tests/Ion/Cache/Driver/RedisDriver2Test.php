<?php

require_once('CacheDriverBase.php');

use Aviat\AnimeClient\Config;
use Aviat\Ion\Di\Container;
use Aviat\Ion\Cache\Driver\RedisDriver;

class CacheRedisDriverTestTwo extends AnimeClient_TestCase {
	use CacheDriverBase;

	protected $driver;

	public function setUp()
	{
		parent::setUp();

		// Setup config with port and password
		$container = new Container();
		$container->set('config', new Config([
			'redis' => [
				'host' => 'localhost',
				'port' => 6379,
				'password' => '',
				'database' => 13,
			]
		]));
		$this->driver = new RedisDriver($container);
	}

	public function tearDown()
	{
		parent::tearDown();
		$this->driver->__destruct();
	}
}