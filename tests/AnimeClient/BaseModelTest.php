<?php

use Aviat\AnimeClient\Model as BaseModel;

class BaseModelTest extends AnimeClient_TestCase {

	public function testBaseModelSanity()
	{
		$baseModel = new BaseModel($this->container);
		$this->assertTrue(is_object($baseModel));
	}
}