<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.2
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests;

use Aura\Router\RouterFactory;
use Aura\Web\WebFactory;
use Aviat\AnimeClient\Controller;
use Aviat\AnimeClient\Controller\{
	Anime as AnimeController,
	Character as CharacterController,
	AnimeCollection as AnimeCollectionController,
	MangaCollection as MangaCollectionController,
	Manga as MangaController
};

class ControllerTest extends AnimeClientTestCase {

	protected $BaseController;

	public function setUp(): void	{
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
		$config->set('database', [
			'type' => 'sqlite',
			'database' => '',
			'file' => ":memory:"
		]);
		$this->container->setInstance('config', $config);

		$this->assertInstanceOf(
			Controller::class,
			new AnimeController($this->container)
		);
		$this->assertInstanceOf(
			Controller::class,
			new MangaController($this->container)
		);
		$this->assertInstanceOf(
			Controller::class,
			new CharacterController($this->container)
		);
		$this->assertInstanceOf(
			Controller::class,
			new AnimeCollectionController($this->container)
		);
		$this->assertInstanceOf(
			Controller::class,
			new MangaCollectionController($this->container)
		);
	}

	public function testBaseControllerSanity()
	{
		$this->assertTrue(\is_object($this->BaseController));
	}

	public function testFormatTitle()
	{
		$this->assertEquals(
			$this->BaseController->formatTitle('foo', 'bar', 'baz'),
			'foo &middot; bar &middot; baz'
		);
	}

}