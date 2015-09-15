<?php
use \Aviat\AnimeClient\Controller;
use \Aura\Web\WebFactory;
use \Aura\Router\RouterFactory;

class ControllerTest extends AnimeClient_TestCase {

	public function setUp()
	{
		parent::setUp();

		// Create Request/Response Objects
		$web_factory = new WebFactory([
			'_GET' => [],
			'_POST' => [],
			'_COOKIE' => [],
			'_SERVER' => $_SERVER,
			'_FILES' => []
		]);
		$this->container->set('request', $web_factory->newRequest());
		$this->container->set('response', $web_factory->newResponse());

		$this->BaseController = new Controller($this->container);
	}

	public function testBaseControllerSanity()
	{
		$this->assertTrue(is_object($this->BaseController));
	}

	public function dataGet()
	{
		return [
			'request' => [
				'key' => 'request',
			],
			'response' => [
				'key' => 'response',
			],
			'config' => [
				'key' => 'config',
			]
		];
	}

	/**
	 * @dataProvider dataGet
	 */
	public function testGet($key)
	{
		$result = $this->BaseController->__get($key);
		$this->assertEquals($this->container->get($key), $result);
	}

	public function testGetNull()
	{
		$result = $this->BaseController->__get('foo');
		$this->assertNull($result);
	}

}