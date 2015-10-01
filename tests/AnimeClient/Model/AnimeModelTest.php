<?php

use Aviat\Ion\Friend;
use Aviat\Ion\Di\ContainerInterface;
use Aviat\AnimeClient\Model\Anime as AnimeModel;

class AnimeMock extends AnimeModel {

	protected $transformed_data_file;

	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);
		$this->transformed_data_file = __DIR__ . "/../../test_data/anime_list/anime-completed-transformed.json";
	}

	protected function _get_list_from_api($status="all")
	{
		$data = json_decode(file_get_contents($this->transformed_data_file), TRUE);
		return $data;
	}
}

class AnimeModelTest extends AnimeClient_TestCase {

	public function setUp()
	{
		parent::setUp();
		$this->animeModel = new Friend(new AnimeMock($this->container));
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

	public function testSortByName()
	{
		$data = $this->animeModel->_get_list_from_api("completed");
	}
}