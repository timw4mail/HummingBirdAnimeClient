<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @copyright   2015 - 2022  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshome.page/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests;

use Aviat\AnimeClient\RoutingBase;
use JetBrains\PhpStorm\ArrayShape;

/**
 * @internal
 */
final class RoutingBaseTest extends AnimeClientTestCase
{
	#[ArrayShape(['empty_segment' => 'array', 'three_segments' => 'array'])]
	public static function dataSegments()
	{
		return [
			'empty_segment' => [
				'requestUri' => '  //      ',
				'path' => '/',
				'segments' => ['', ''],
				'lastSegment' => NULL,
			],
			'three_segments' => [
				'requestUri' => '/anime/watching/list  ',
				'path' => '/anime/watching/list',
				'segments' => ['', 'anime', 'watching', 'list'],
				'lastSegment' => 'list',
			],
		];
	}

		#[\PHPUnit\Framework\Attributes\DataProvider('dataSegments')]
	 public function testSegments(string $requestUri, string $path, array $segments, ?string $lastSegment): void
	 {
	 	$this->setSuperGlobals([
	 		'_SERVER' => [
	 			'REQUEST_URI' => $requestUri,
	 		],
	 	]);

	 	$routingBase = new RoutingBase($this->container);

	 	$this->assertSame($path, $routingBase->path(), 'Path is invalid');
	 	$this->assertSame($segments, $routingBase->segments(), 'Segments array is invalid');
	 	$this->assertEquals($lastSegment, $routingBase->lastSegment(), 'Last segment is invalid');

	 	foreach ($segments as $i => $value)
	 	{
	 		$this->assertEquals($value, $routingBase->getSegment($i), "Segment {$i} is invalid");
	 	}
	 }
}
