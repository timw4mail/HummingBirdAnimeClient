<?php

use Aviat\AnimeClient\Command\BaseCommand;
use Aviat\Ion\Friend;
use ConsoleKit\Console;

class BaseCommandTest extends AnimeClient_TestCase {
	public function setUp()
	{
		$this->base = new BaseCommand(new Console());
		$this->friend = new Friend($this->base);
	}

	public function testSetupContainer()
	{
		$container = $this->friend->setupContainer();
		$this->assertInstanceOf('Aviat\Ion\Di\Container', $container);
	}
}