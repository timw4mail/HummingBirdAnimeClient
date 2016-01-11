<?php
use GuzzleHttp\Psr7\Response;

use Aviat\Ion\Friend;
use Aviat\Ion\Json;
use Aviat\Ion\Di\ContainerInterface;
use Aviat\AnimeClient\Model\Manga as MangaModel;
use Aviat\AnimeClient\Hummingbird\Enum\MangaReadingStatus;

class MangaModelTest extends AnimeClient_TestCase {

	public function setUp()
	{
		parent::setUp();
		$this->model = new Friend(new TestMangaModel($this->container));
		$this->mockDir = __DIR__ . '/../../test_data/manga_list';
	}

	public function testZipperLists()
	{
		$raw_data = Json::decodeFile($this->mockDir . '/manga.json');
		$expected = Json::decodeFile($this->mockDir . '/manga-zippered.json');

		$this->assertEquals($expected, $this->model->zipper_lists($raw_data));
	}

	public function testMapByStatus()
	{
		$original = Json::decodeFile($this->mockDir . '/manga-transformed.json');
		$expected = Json::decodeFile($this->mockDir . '/manga-mapped.json');
		$actual = $this->model->map_by_status($original);

		$this->assertEquals($expected, $actual);
	}

	public function testGetListFromApi()
	{
		$data = file_get_contents($this->mockDir . '/manga.json');
		$client = $this->getMockClient(200, [
			'Content-type' => 'application/json'
		], $data);
		$this->model->__set('client', $client);

		$reflect = new ReflectionClass($this->model);
		$constants = $reflect->getConstants();

		$expected_all = Json::decodeFile($this->mockDir . '/manga-mapped.json');

		$this->assertEquals($expected_all, $this->model->_get_list_from_api());

		foreach($constants as $name => $value)
		{
			$key = $reflect->getConstant($name);
			$this->assertEquals($expected_all[$key], $this->model->_get_list_from_api($key));
		}
	}

	public function testGetList()
	{
		if (($var = getenv('CI')))
		{
			$this->markTestSkipped();
		}

		$data = file_get_contents($this->mockDir . '/manga.json');
		$client = $this->getMockClient(200, [
			'Content-type' => 'application/json'
		], $data);
		$this->model->__set('client', $client);

		$expected = Json::decodeFile($this->mockDir . '/get-all-lists.json');
		$this->assertEquals($expected['Reading'], $this->model->get_list('Reading'));
	}

	public function testGetAllLists()
	{
		if (($var = getenv('CI')))
		{
			$this->markTestSkipped();
		}

		$data = file_get_contents($this->mockDir . '/manga.json');
		$client = $this->getMockClient(200, [
			'Content-type' => 'application/json'
		], $data);
		$this->model->__set('client', $client);

		$expected = Json::decodeFile($this->mockDir . '/get-all-lists.json');
		$this->assertEquals($expected, $this->model->get_all_lists());
	}
}