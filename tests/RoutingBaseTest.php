<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2017  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

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