<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.2
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2019  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests;

use Aviat\AnimeClient\RoutingBase;

class RoutingBaseTest extends AnimeClientTestCase {

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
	public function testSegments(string $requestUri, string $path, array $segments, $lastSegment): void
	{
		$this->setSuperGlobals([
			'_SERVER' => [
				'REQUEST_URI' => $requestUri
			]
		]);

		$routingBase = new RoutingBase($this->container);

		$this->assertEquals($path, $routingBase->path(), "Path is invalid");
		$this->assertEquals($segments, $routingBase->segments(), "Segments array is invalid");
		$this->assertEquals($lastSegment, $routingBase->lastSegment(), "Last segment is invalid");

		foreach($segments as $i => $value)
		{
			$this->assertEquals($value, $routingBase->getSegment($i), "Segment {$i} is invalid");
		}
	}
}