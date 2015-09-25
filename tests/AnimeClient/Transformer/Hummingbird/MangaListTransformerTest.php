<?php

use Aviat\AnimeClient\Transformer\Hummingbird\MangaListTransformer;

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
		$orig_json = json_decode(file_get_contents($this->start_file), TRUE);
		$expected = json_decode(file_get_contents($this->res_file), TRUE);

		$actual = $this->transformer->transform_collection($orig_json);
		$this->assertEquals($expected, $actual);
	}
}