<?php

use \AnimeClient\BaseDBModel;

class BaseDBModelTest extends AnimeClient_TestCase {

	public function testBaseDBModelSanity()
	{
		$baseDBModel = new BaseDBModel($this->config);
		$this->assertTrue(is_object($baseDBModel));
	}
}