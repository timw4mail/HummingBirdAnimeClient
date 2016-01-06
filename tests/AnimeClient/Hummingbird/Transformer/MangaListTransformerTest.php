<?php

use Aviat\AnimeClient\Hummingbird\Transformer\MangaListTransformer;

class MangaListTransformerTest extends AnimeClient_TestCase {

	public function setUp()
	{
		parent::setUp();
		$this->start_file = __DIR__ . '/../../../test_data/manga_list/manga-zippered.json';
		$this->res_file = __DIR__ . '/../../../test_data/manga_list/manga-transformed.json';
		$this->transformer = new MangaListTransformer();
	}


	public function testTransform()
	{
		$orig_json = json_file_decode($this->start_file);
		$expected = json_file_decode($this->res_file);

		$actual = $this->transformer->transform_collection($orig_json);
		$this->assertEquals($expected, $actual);
	}

	public function dataUntransform()
	{
		return [
			'same_rating' => [
				'orig' => [
					'id' => 401735,
					'manga_id' => "love-hina",
					'status' => "Plan to Read",
					'chapters_read' => 16,
					'volumes_read' => 2,
					'rereading' => true,
					'reread_count' => 1,
					'notes' => "Some text notes",
					'old_rating' => 7,
					'new_rating' => 7,
				],
				'expected' => [
					'id' => 401735,
					'manga_id' => "love-hina",
					'status' => "Plan to Read",
					'chapters_read' => 16,
					'volumes_read' => 2,
					'rereading' => true,
					'reread_count' => 1,
					'notes' => "Some text notes",
				]
			],
			'update_rating' => [
				'orig' => [
					'id' => 401735,
					'manga_id' => "love-hina",
					'status' => "Plan to Read",
					'chapters_read' => 16,
					'volumes_read' => 2,
					'rereading' => true,
					'reread_count' => 1,
					'notes' => "Some text notes",
					'old_rating' => 7,
					'new_rating' => 8,
				],
				'expected' => [
					'id' => 401735,
					'manga_id' => "love-hina",
					'status' => "Plan to Read",
					'chapters_read' => 16,
					'volumes_read' => 2,
					'rereading' => true,
					'reread_count' => 1,
					'notes' => "Some text notes",
					'rating' => 4,
				]
			],
			'remove_rating' => [
				'orig' => [
					'id' => 401735,
					'manga_id' => "love-hina",
					'status' => "Plan to Read",
					'chapters_read' => 16,
					'volumes_read' => 2,
					'rereading' => true,
					'reread_count' => 1,
					'notes' => "Some text notes",
					'old_rating' => 7,
					'new_rating' => 0,
				],
				'expected' => [
					'id' => 401735,
					'manga_id' => "love-hina",
					'status' => "Plan to Read",
					'chapters_read' => 16,
					'volumes_read' => 2,
					'rereading' => true,
					'reread_count' => 1,
					'notes' => "Some text notes",
					'rating' => 3.5,
				]
			]
		];
	}

	/**
	 * @dataProvider dataUntransform
	 */
	public function testUntransform($orig, $expected)
	{
		$actual = $this->transformer->untransform($orig);
		$this->assertEquals($expected, $actual);
	}
}