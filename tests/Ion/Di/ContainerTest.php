<?php

use Aviat\Ion\Di\Container;
use Aviat\Ion\Di\Exception\ContainerException;
use Monolog\Logger;
use Monolog\Handler\TestHandler;
use Monolog\Handler\NullHandler;


class ContainerTest extends AnimeClient_TestCase {

	public function setUp()
	{
		$this->container = new Container();
	}

	public function dataGetWithException()
	{
		return [
			'Bad index type: number' => [
				'id' => 42,
				'exception' => 'Aviat\Ion\Di\Exception\ContainerException',
				'message' => 'Id must be a string'
			],
			'Bad index type: array' => [
				'id' => [],
				'exception' => 'Aviat\Ion\Di\Exception\ContainerException',
				'message' => 'Id must be a string'
			],
			'Non-existent id' => [
				'id' => 'foo',
				'exception' => 'Aviat\Ion\Di\Exception\NotFoundException',
				'message' => "Item 'foo' does not exist in container."
			]
		];
	}

	/**
	 * @dataProvider dataGetWithException
	 */
	public function testGetWithException($id, $exception, $message)
	{
		try
		{
			$this->container->get($id);
		}
		catch(ContainerException $e)
		{
			$this->assertInstanceOf($exception, $e);
			$this->assertEquals($message, $e->getMessage());
		}
	}

	public function testLoggerMethods()
	{
		// Does the container have the default logger?
		$this->assertFalse($this->container->hasLogger());
		$this->assertFalse($this->container->hasLogger('default'));

		$logger1 = new Logger('default');
		$logger2 = new Logger('testing');
		$logger1->pushHandler(new NullHandler());
		$logger2->pushHandler(new TestHandler());

		// Set the logger channels
		$this->container->setLogger($logger1);
		$this->container->setLogger($logger2, 'test');

		$this->assertEquals($logger1, $this->container->getLogger('default'));
		$this->assertEquals($logger2, $this->container->getLogger('test'));
		$this->assertNull($this->container->getLogger('foo'));

		$this->assertTrue($this->container->hasLogger());
		$this->assertTrue($this->container->hasLogger('default'));
		$this->assertTrue($this->container->hasLogger('test'));
	}
}