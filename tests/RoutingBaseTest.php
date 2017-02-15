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
				'request_uri' => '  //      ',
				'path' => '/',
				'segments' => ['', ''],
				'last_segment' => NULL
			],
			'three_segments' => [
				'request_uri' => '/anime/watching/list  ',
				'path' => '/anime/watching/list',
				'segments' => ['', 'anime', 'watching', 'list'],
				'last_segment' => 'list'
			]
		];
	}

	/**
	 * @dataProvider dataSegments
	 */
	public function testSegments($request_uri, $path, $segments, $last_segment)
	{
		$this->setSuperGlobals([
			'_SERVER' => [
				'REQUEST_URI' => $request_uri
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