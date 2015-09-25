<?php

use \AnimeClient\Config;

class FunctionsTest extends AnimeClient_TestCase {

	/**
	 * Basic sanity test for _dir function
	 */
	public function testDir()
	{
		$this->assertEquals('foo' . DIRECTORY_SEPARATOR . 'bar', _dir('foo', 'bar'));
	}

	public function testIsSelected()
	{
		// Failure to match
		$this->assertEquals('', is_selected('foo', 'bar'));

		// Matches
		$this->assertEquals('selected', is_selected('foo', 'foo'));
	}

	public function testIsNotSelected()
	{
		// Failure to match
		$this->assertEquals('selected', is_not_selected('foo', 'bar'));

		// Matches
		$this->assertEquals('', is_not_selected('foo', 'foo'));
	}
}
