<?php

use AnimeClient\Base\Model as BaseModel;
use AnimeClient\Base\Container;

class BaseModelTest extends AnimeClient_TestCase {

	public function testBaseModelSanity()
	{
		$baseModel = new BaseModel($this->container);
		$this->assertTrue(is_object($baseModel));
	}
}