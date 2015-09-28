<?php

use Aviat\Ion\Friend;
use Aviat\AnimeClient\Transformer\Hummingbird\AnimeListTransformer;

class AnimeListTransformerTest extends AnimeClient_TestCase {

	public function setUp()
	{
		parent::setUp();
		$this->start_file = __DIR__ . '/../../../test_data/anime_list/anime-completed.json';
		$this->res_file = __DIR__ . '/../../../test_data/anime_list/anime-completed-transformed.json';
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
		$actual = $this->transformerFriend->linearize_genres($original);
		$this->assertEquals($expected, $actual);
	}

	public function testTransform()
	{
		$json = json_decode(file_get_contents($this->start_file), TRUE);
		$expected = json_decode(file_get_contents($this->res_file), TRUE);
		$actual = $this->transformer->transform_collection($json);
//file_put_contents($this->res_file, json_encode($actual));
		$this->assertEquals($expected, $actual);
	}
}