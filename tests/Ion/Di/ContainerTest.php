<?php

use Aviat\Ion\Di\Container;
use Aviat\Ion\Di\Exception\ContainerException;


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

}