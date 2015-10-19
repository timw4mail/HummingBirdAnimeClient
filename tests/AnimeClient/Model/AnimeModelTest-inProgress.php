<?php

use Aviat\Ion\Friend;
use Aviat\Ion\Di\ContainerInterface;
use Aviat\AnimeClient\Model\Anime as AnimeModel;

class AnimeModelTest extends AnimeClient_TestCase {

	public function setUp()
	{
		parent::setUp();
		$this->animeModel = new Friend(new TestAnimeModel($this->container));
	}

	protected function _pluck_anime_titles($array)
	{
		$out = [];
		foreach($array as $index => $item)
		{
			$out[] = $item['anime']['title'];
		}

		return $out;
	}

	/*public function testSortByName()
	{
		$data = $this->animeModel->_get_list_from_api("completed");
	}*/
}