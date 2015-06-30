<?php
use \AnimeClient\BaseController;
use \Aura\Web\WebFactory;
use \Aura\Router\RouterFactory;

class BaseControllerTest extends AnimeClient_TestCase {

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
		$request = $web_factory->newRequest();
		$response = $web_factory->newResponse();

		$this->BaseController = new BaseController($this->config, [$request, $response]);
	}

	public function testBaseControllerSanity()
	{
		$this->assertTrue(is_object($this->BaseController));
	}

}