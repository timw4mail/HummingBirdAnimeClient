<?php

use Aviat\Ion\Friend;
use Aviat\Ion\Json;
use Aviat\AnimeClient\Hummingbird\Transformer\AnimeListTransformer;

class AnimeListTransformerTest extends AnimeClient_TestCase {

	public function setUp()
	{
		parent::setUp();
		$this->start_file = __DIR__ . '/../test_data/anime_list/anime-completed.json';
		$this->res_file = __DIR__ . '/../test_data/anime_list/anime-completed-transformed.json';
		$this->transformer = new AnimeListTransformer();
		$this->transformerFriend = new Friend($this->transformer);
	}

	public function dataLinearizeGenres()
	{
		return [
			[
				'original' => [
					['name' => 'Action'],
					['name' => 'Comedy'],
					['name' => 'Magic'],
					['name' => 'Fantasy'],
					['name' => 'Mahou Shoujo']
				],
				'expected' => ['Action', 'Comedy', 'Magic', 'Fantasy', 'Mahou Shoujo']
			]
		];
	}

	/**
	 * @dataProvider dataLinearizeGenres
	 */
	public function testLinearizeGenres($original, $expected)
	{
		$actual = $this->transformerFriend->linearizeGenres($original);
		$this->assertEquals($expected, $actual);
	}

	public function testTransform()
	{
		$json = Json::decodeFile($this->start_file);
		$expected = Json::decodeFile($this->res_file);
		$actual = $this->transformer->transformCollection($json);
		$this->assertEquals($expected, $actual);
	}
}