<?php

use \AnimeClient\BaseModel;

class BaseModelTest extends AnimeClient_TestCase {

	public function testBaseModelSanity()
	{
		$baseModel = new BaseModel($this->config);
		$this->assertTrue(is_object($baseModel));
	}
}