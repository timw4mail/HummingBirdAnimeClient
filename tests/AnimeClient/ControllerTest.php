<?php
use \Aviat\AnimeClient\Controller;
use Aviat\AnimeClient\Controller\Anime as AnimeController;
use Aviat\AnimeClient\Controller\Manga as MangaController;
use Aviat\AnimeClient\Controller\Collection as CollectionController;
use \Aura\Web\WebFactory;
use \Aura\Router\RouterFactory;

class ControllerTest extends AnimeClient_TestCase {

	public function setUp()
	{
		parent::setUp();

		// Create Request/Response Objects
		$web_factory = new WebFactory([
			'_GET' => [],
			'_POST' => [],
			'_COOKIE' => [],
			'_SERVER' => $_SERVER,
			'_FILES' => []
		]);
		$this->container->set('request', $web_factory->newRequest());
		$this->container->set('response', $web_factory->newResponse());


		$this->BaseController = new Controller($this->container);
	}

	public function testControllersSanity()
	{
		$config = $this->container->get('config');
		$config->set(['database', 'collection'], [
			'type' => 'sqlite',
			'database' => '',
			'file' => ":memory:"
		]);
		$this->container->set('config', $config);

		$this->assertInstanceOf(
			'Aviat\AnimeClient\Controller',
			new AnimeController($this->container)
		);
		$this->assertInstanceOf(
			'Aviat\AnimeClient\Controller',
			new MangaController($this->container)
		);
		$this->assertInstanceOf(
			'Aviat\AnimeClient\Controller',
			new CollectionController($this->container)
		);
	}

	public function testBaseControllerSanity()
	{
		$this->assertTrue(is_object($this->BaseController));
	}

	public function dataGet()
	{
		return [
			'response' => [
				'key' => 'response',
			],
			'config' => [
				'key' => 'config',
			]
		];
	}

	/**
	 * @dataProvider dataGet
	 */
	public function testGet($key)
	{
		$result = $this->BaseController->__get($key);
		$this->assertEquals($this->container->get($key), $result);
	}

	public function testGetNull()
	{
		$result = $this->BaseController->__get('foo');
		$this->assertNull($result);
	}

}