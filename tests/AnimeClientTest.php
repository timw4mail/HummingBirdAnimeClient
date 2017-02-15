<?php

namespace Aviat\AnimeClient\Tests;

class AnimeClientTest extends AnimeClientTestCase {
	/**
	 * Basic sanity test for _dir function
	 */
	public function testDir()
	{
		$this->assertEquals('foo' . \DIRECTORY_SEPARATOR . 'bar', \_dir('foo', 'bar'));
	}
}
