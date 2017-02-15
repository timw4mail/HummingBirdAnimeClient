<?php

namespace Aviat\AnimeClient\Tests;

use Aviat\AnimeClient\RoutingBase;

class RoutingBaseTest extends AnimeClient_TestCase {

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
				'last_segment' => NULL
			],
			'three_segments' => [
				'requestUri' => '/anime/watching/list  ',
				'path' => '/anime/watching/list',
				'segments' => ['', 'anime', 'watching', 'list'],
				'last_segment' => 'list'
			]
		];
	}

	/**
	 * @dataProvider dataSegments
	 */
	public function testSegments($requestUri, $path, $segments, $last_segment)
	{
		$this->setSuperGlobals([
			'_SERVER' => [
				'REQUEST_URI' => $requestUri
			]
		]);

		$this->assertEquals($path, $this->routingBase->path(), "Path is invalid");
		$this->assertEquals($segments, $this->routingBase->segments(), "Segments array is invalid");
		$this->assertEquals($last_segment, $this->routingBase->last_segment(), "Last segment is invalid");

		foreach($segments as $i => $value)
		{
			$this->assertEquals($value, $this->routingBase->get_segment($i), "Segment {$i} is invalid");
		}
	}
}