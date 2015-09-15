<?php

use Aviat\AnimeClient\Model\DB as BaseDBModel;

class BaseDBModelTest extends AnimeClient_TestCase {

	public function testBaseDBModelSanity()
	{
		$baseDBModel = new BaseDBModel($this->container);
		$this->assertTrue(is_object($baseDBModel));
	}
}