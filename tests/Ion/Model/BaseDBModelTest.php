<?php

use Aviat\Ion\Model\DB as BaseDBModel;

class BaseDBModelTest extends AnimeClient_TestCase {

	public function testBaseDBModelSanity()
	{
		$baseDBModel = new BaseDBModel($this->container->get('config'));
		$this->assertTrue(is_object($baseDBModel));
	}
}