<?php

class FunctionsTest extends AnimeClient_TestCase {

	/**
	 * Basic sanity test for _dir function
	 */
	public function testDir()
	{
		$this->assertEquals('foo'.DIRECTORY_SEPARATOR.'bar', _dir('foo', 'bar'));
	}

	public function testIsSelected()
	{

	}

	public function testIsNotSelected()
	{

	}

}