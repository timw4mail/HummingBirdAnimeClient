<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2017  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests;

use Aura\Router\RouterFactory;
use Aura\Web\WebFactory;
use Aviat\AnimeClient\Controller;
use Aviat\AnimeClient\Controller\{
	Anime as AnimeController,
	Character as CharacterController,
	Collection as CollectionController,
	Manga as MangaController
};

class ControllerTest extends AnimeClientTestCase {

	protected $BaseController;

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
			new CharacterController($this->container)
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