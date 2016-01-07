<?php

use Aviat\Ion\Json;
use Aviat\AnimeClient\Hummingbird\Transformer\MangaListsZipper;

class MangaListsZipperTest extends AnimeClient_TestCase {

	protected $start_file = '';
	protected $res_file = '';

	public function setUp()
	{
		$this->start_file = __DIR__ . '/../../../test_data/manga_list/manga.json';
		$this->res_file = __DIR__ . '/../../../test_data/manga_list/manga-zippered.json';

		$json = Json::decodeFile($this->start_file);
		$this->mangaListsZipper = new MangaListsZipper($json);
	}

	public function testTransform()
	{
		$zippered_json = Json::decodeFile($this->res_file);
		$transformed = $this->mangaListsZipper->transform();

		$this->assertEquals($zippered_json, $transformed);
	}

}