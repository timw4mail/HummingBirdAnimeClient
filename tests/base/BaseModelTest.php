<?php

class BaseModelTest extends AnimeClient_TestCase {

	public function testBaseModelSanity()
	{
		$baseModel = new BaseModel();
		$this->assertTrue(is_object($baseModel));
	}
}