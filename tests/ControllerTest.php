<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests;

use Aura\Router\RouterFactory;
use Aura\Web\WebFactory;
use Aviat\AnimeClient\Controller;
use Aviat\AnimeClient\Controller\{
	Anime as AnimeController,
	Collection as CollectionController,
	Manga as MangaController
};

class ControllerTest extends AnimeClientTestCase {

	public function setUp()
	{
		parent::setUp();

		// Create Request/Response Objects
		$_SERVER['HTTP_REFERER'] = '';
		$this->setSuperGlobals([
			'_GET' => [],
			'_POST' => [],
			'_COOKIE' => [],
			'_SERVER' => $_SERVER,
			'_FILES' => []
		]);

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
		$this->container->setInstance('config', $config);

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