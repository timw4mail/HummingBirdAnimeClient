<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2021  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\Ion\Tests\Transformer;

use Aviat\Ion\Tests\IonTestCase;
use Aviat\Ion\Tests\{TestTransformer, TestTransformerUntransform};

class AbstractTransformerTest extends IonTestCase {

	protected $transformer;
	protected $untransformer;


	public function setUp(): void	{
		$this->transformer = new TestTransformer();
		$this->untransformer = new TestTransformerUntransform();
	}

	public function dataTransformCollection()
	{
		return [
			'object' => [
				'original' => [
					(object)[
						['name' => 'Comedy'],
						['name' => 'Romance'],
						['name' => 'School'],
						['name' => 'Harem']
					],
					(object)[
						['name' => 'Action'],
						['name' => 'Comedy'],
						['name' => 'Magic'],
						['name' => 'Fantasy'],
						['name' => 'Mahou Shoujo']
					],
					(object)[
						['name' => 'Comedy'],
						['name' => 'Sci-Fi']
					]
				],
				'expected' => [
					['Comedy', 'Romance', 'School', 'Harem'],
					['Action', 'Comedy', 'Magic', 'Fantasy', 'Mahou Shoujo'],
					['Comedy', 'Sci-Fi']
				]
			],
			'array' => [
				'original' => [
					[
						['name' => 'Comedy'],
						['name' => 'Romance'],
						['name' => 'School'],
						['name' => 'Harem']
					],
					[
						['name' => 'Action'],
						['name' => 'Comedy'],
						['name' => 'Magic'],
						['name' => 'Fantasy'],
						['name' => 'Mahou Shoujo']
					],
					[
						['name' => 'Comedy'],
						['name' => 'Sci-Fi']
					]
				],
				'expected' => [
					['Comedy', 'Romance', 'School', 'Harem'],
					['Action', 'Comedy', 'Magic', 'Fantasy', 'Mahou Shoujo'],
					['Comedy', 'Sci-Fi']
				]
			],
		];
	}

	public function dataUnTransformCollection()
	{
		return [
			'object' => [
				'original' => [
					(object)['Comedy', 'Romance', 'School', 'Harem'],
					(object)['Action', 'Comedy', 'Magic', 'Fantasy', 'Mahou Shoujo'],
					(object)['Comedy', 'Sci-Fi']
				],
				'expected' => [
					['Comedy', 'Romance', 'School', 'Harem'],
					['Action', 'Comedy', 'Magic', 'Fantasy', 'Mahou Shoujo'],
					['Comedy', 'Sci-Fi']
				]
			],
			'array' => [
				'original' => [
					['Comedy', 'Romance', 'School', 'Harem'],
					['Action', 'Comedy', 'Magic', 'Fantasy', 'Mahou Shoujo'],
					['Comedy', 'Sci-Fi']
				],
				'expected' => [
					['Comedy', 'Romance', 'School', 'Harem'],
					['Action', 'Comedy', 'Magic', 'Fantasy', 'Mahou Shoujo'],
					['Comedy', 'Sci-Fi']
				]
			]
		];
	}

	public function testTransform()
	{
		$data = $this->dataTransformCollection();
		$original = $data['object']['original'][0];
		$expected = $data['object']['expected'][0];

		$actual = $this->transformer->transform($original);
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @dataProvider dataTransformCollection
	 */
	public function testTransformCollection($original, $expected)
	{
		$actual = $this->transformer->transformCollection($original);
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @dataProvider dataUnTransformCollection
	 */
	public function testUntransformCollection($original, $expected)
	{
		$actual = $this->untransformer->untransformCollection($original);
		$this->assertEquals($expected, $actual);
	}

	/**
	 * @dataProvider dataUnTransformCollection
	 */
	public function testUntransformCollectionWithException($original, $expected)
	{
		$this->expectException(\BadMethodCallException::class);
		$this->transformer->untransformCollection($original);
	}
}