<?php

class CoreTest extends AnimeClient_TestCase {

	public function testPHPVersion()
	{
		$this->assertTrue(version_compare(PHP_VERSION, "5.4", "ge"));
	}

	public function testRequirements()
	{
		// Check required extensions
		$this->assertTrue(extension_loaded('gd'));
		$this->assertTrue(extension_loaded('mcrypt'));

		// Check for pdo_sqlite
		$this->assertTrue(class_exists('PDO'));
		$drivers = PDO::getAvailableDrivers();
		$this->assertTrue(in_array('sqlite', $drivers));
	}

}