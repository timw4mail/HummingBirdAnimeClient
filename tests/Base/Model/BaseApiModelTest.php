<?php

use Aviat\AnimeClient\Base\Container;
use Aviat\AnimeClient\Base\Model\API as BaseApiModel;

class MockBaseApiModel extends BaseApiModel {

	public function __construct(Container $container)
	{
		parent::__construct($container);
	}

	public function __get($key)
	{
		return $this->$key;
	}
}

class BaseApiModelTest extends AnimeClient_TestCase {

	public function testBaseApiModelSanity()
	{
		$baseApiModel = new MockBaseApiModel($this->container);

		// Some basic type checks for class memebers
		$this->assertInstanceOf('\Aviat\AnimeClient\Base\Model', $baseApiModel);
		$this->assertInstanceOf('\Aviat\AnimeClient\Base\Model\API', $baseApiModel);

		$this->assertInstanceOf('\GuzzleHttp\Client', $baseApiModel->client);
		$this->assertInstanceOf('\GuzzleHttp\Cookie\CookieJar', $baseApiModel->cookieJar);

		$this->assertTrue(is_string($baseApiModel->base_url));
		$this->assertTrue(empty($baseApiModel->base_url));
	}

}