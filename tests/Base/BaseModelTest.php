<?php

use Aviat\AnimeClient\Base\Model as BaseModel;
use Aviat\AnimeClient\Base\Container;

class BaseModelTest extends AnimeClient_TestCase {

	public function testBaseModelSanity()
	{
		$baseModel = new BaseModel($this->container);
		$this->assertTrue(is_object($baseModel));
	}
}