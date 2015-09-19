<?php

use Aviat\Ion\Di\Container;
use Aviat\Ion\Di\ContainerAware;
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Di\Exception\ContainerException;

class Aware {
	use ContainerAware;
	
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}
}


class ContainerAwareTest extends AnimeClient_TestCase {
	
	public function setUp()
	{
		$this->container = new Container();
		$this->aware = new Aware($this->container);
	}
	
	public function testContainerAwareTrait()
	{
		// The container was set in setup
		// check that the get method returns the same
		$this->assertSame($this->container, $this->aware->getContainer());
		
		$container2 = new Container([
			'foo' => 'bar',
			'baz' => 'foobar'
		]);
		$this->aware->setContainer($container2);
		$this->assertSame($container2, $this->aware->getContainer());
	}
}