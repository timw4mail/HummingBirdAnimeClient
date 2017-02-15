<?php

namespace Aviat\AnimeClient\Tests;

use Aviat\AnimeClient\RoutingBase;

class RoutingBaseTest extends AnimeClientTestCase {

	public function setUp()
	{
		parent::setUp();
		$this->routingBase = new RoutingBase($this->container);
	}

	public function dataSegments()
	{
		return [
			'empty_segment' => [
				'requestUri' => '  //      ',
				'path' => '/',
				'segments' => ['', ''],
				'lastSegment' => NULL
			],
			'three_segments' => [
				'requestUri' => '/anime/watching/list  ',
				'path' => '/anime/watching/list',
				'segments' => ['', 'anime', 'watching', 'list'],
				'lastSegment' => 'list'
			]
		];
	}

	/**
	 * @dataProvider dataSegments
	 */
	public function testSegments($requestUri, $path, $segments, $lastSegment)
	{
		$this->setSuperGlobals([
			'_SERVER' => [
				'REQUEST_URI' => $requestUri
			]
		]);

		$this->assertEquals($path, $this->routingBase->path(), "Path is invalid");
		$this->assertEquals($segments, $this->routingBase->segments(), "Segments array is invalid");
		$this->assertEquals($lastSegment, $this->routingBase->lastSegment(), "Last segment is invalid");

		foreach($segments as $i => $value)
		{
			$this->assertEquals($value, $this->routingBase->getSegment($i), "Segment {$i} is invalid");
		}
	}
}