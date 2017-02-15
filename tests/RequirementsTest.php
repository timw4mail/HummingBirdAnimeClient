<?php

namespace Aviat\AnimeClient\Tests;

use PDO;

class RequirementsTest extends AnimeClient_TestCase {

	public function testPHPVersion()
	{
		$this->assertTrue(version_compare(PHP_VERSION, "5.4", "ge"));
	}

	public function testHasPDO()
	{
		$this->assertTrue(class_exists('PDO'));
	}

	public function testHasPDOSqlite()
	{
		$drivers = PDO::getAvailableDrivers();
		$this->assertTrue(in_array('sqlite', $drivers));
	}
}