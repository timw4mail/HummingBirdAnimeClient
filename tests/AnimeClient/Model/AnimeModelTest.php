<?php
use Aviat\Ion\Friend;
use Aviat\Ion\Di\ContainerInterface;
use Aviat\AnimeClient\Model\Anime as AnimeModel;

class AnimeModelTest extends AnimeClient_TestCase {

	public function setUp()
	{
		parent::setUp();
		$this->animeModel = new Friend(new TestAnimeModel($this->container));
		$this->mockDir = __DIR__ . '/../../test_data/anime_list/search_mocks';
	}

	public function dataSearch()
	{
		return [
			'nonsense search' => [
				'search' => 'foo',
			],
			'search for common series' => [
				'search' => 'Fate',
			],
			'search for weird series' => [
				'search' => 'Twintails',
			]
		];
	}

	/**
	 * @dataProvider dataSearch
	 */
	public function testSearch($search)
	{
		// Mock requests
		$json = file_get_contents(_dir($this->mockDir, "{$search}.json"));
		$client = $this->getMockClient(200, [
			'Content-Type' => 'application/json'
		], $json);
		$this->animeModel->__set('client', $client);

		$actual = $this->animeModel->search($search);
		$this->assertEquals(json_decode($json, TRUE), $actual);
	}

	public function testSearchBadResponse()
	{
		$client = $this->getMockClient(400, [
			'Content-Type' => 'application/json'
		], "[]");
		$this->animeModel->__set('client', $client);

		$this->setExpectedException('\RuntimeException');
		$this->animeModel->search('');
	}
}