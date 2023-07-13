<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.1
 *
 * @copyright   2015 - 2023  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests;

use Aviat\AnimeClient\Controller;
use Aviat\AnimeClient\Controller\{
	Anime as AnimeController,
	AnimeCollection as AnimeCollectionController,
	Character as CharacterController,
	Manga as MangaController // MangaCollection as MangaCollectionController,
};

/**
 * @internal
 */
final class ControllerTest extends AnimeClientTestCase
{
	protected $BaseController;

	protected function setUp(): void
	{
		parent::setUp();

		// Create Request/Response Objects
		$GLOBALS['_SERVER']['HTTP_REFERER'] = '';
		$this->setSuperGlobals([
			'_GET' => [],
			'_POST' => [],
			'_COOKIE' => [],
			'_SERVER' => $GLOBALS['_SERVER'],
			'_FILES' => [],
		]);

		$this->BaseController = new Controller($this->container);
	}

	public function testControllersSanity()
	{
		$config = $this->container->get('config');
		$config->set('database', [
			'type' => 'sqlite',
			'database' => '',
			'file' => ':memory:',
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
		/* $this->assertInstanceOf(
			Controller::class,
			new MangaCollectionController($this->container)
		); */
	}

	public function testBaseControllerSanity()
	{
		$this->assertIsObject($this->BaseController);
	}

	public function testFormatTitle()
	{
		$this->assertSame(
			$this->BaseController->formatTitle('foo', 'bar', 'baz'),
			'foo &middot; bar &middot; baz'
		);
	}
}
