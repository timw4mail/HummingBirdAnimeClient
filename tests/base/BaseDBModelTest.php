<?php

class BaseDBModelTest extends AnimeClient_TestCase {

	public function testBaseDBModelSanity()
	{
		$baseDBModel = new BaseDBModel();
		$this->assertTrue(is_object($baseDBModel));
	}
}