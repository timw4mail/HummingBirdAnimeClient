<?php

class MockBaseApiModel extends BaseApiModel {

	public function __construct()
	{
		parent::__construct();
	}

	public function __get($key)
	{
		return $this->$key;
	}
}

class BaseApiModelTest extends AnimeClient_TestCase {

	public function testBaseApiModelSanity()
	{
		$baseApiModel = new MockBaseApiModel();

		// Some basic type checks for class memebers
		$this->assertInstanceOf('BaseModel', $baseApiModel);
		$this->assertInstanceOf('BaseApiModel', $baseApiModel);

		$this->assertInstanceOf('\GuzzleHttp\Client', $baseApiModel->client);
		$this->assertInstanceOf('\GuzzleHttp\Cookie\CookieJar', $baseApiModel->cookieJar);

		$this->assertTrue(is_string($baseApiModel->base_url));
		$this->assertTrue(empty($baseApiModel->base_url));
	}

}