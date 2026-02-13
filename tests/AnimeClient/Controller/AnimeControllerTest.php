<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\Controller;

use Aviat\AnimeClient\Controller\Anime as AnimeController;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * @internal
 */
#[AllowMockObjectsWithoutExpectations]
final class AnimeControllerTest extends AnimeClientTestCase
{
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
	}

	public function testAnimeList(): void
	{
		$model = $this->createMock(\Aviat\AnimeClient\Model\Anime::class);
		$model
			->expects($this->once())
			->method('getList')
			->willReturn([]);
		$this->container->setInstance('anime-model', $model);

		$controller = new AnimeController($this->container);

		// Capture output to avoid SapiEmitter issues
		ob_start();
		$controller->index('watching', 'list');
		ob_end_clean();

		$this->assertTrue(true); // If it didn't throw, it's okay for now
	}

	public function testDetails(): void
	{
		$model = $this->createMock(\Aviat\AnimeClient\Model\Anime::class);
		$model
			->expects($this->once())
			->method('getAnime')
			->with('test-slug')
			->willReturn(\Aviat\AnimeClient\Types\Anime::from([
				'title' => 'Test',
				'titles' => ['Test'],
				'episode_count' => 12,
				'episode_length' => 24,
				'cover_image' => 'test.jpg',
				'show_type' => 'TV',
				'age_rating' => 'G',
				'genres' => [],
				'streaming_links' => [],
			]));
		$this->container->setInstance('anime-model', $model);

		$controller = new AnimeController($this->container);

		ob_start();
		$controller->details('test-slug');
		ob_end_clean();

		$this->assertTrue(true);
	}

	public function testAddForm(): void
	{
		$auth = $this->createMock(\Aviat\AnimeClient\API\Kitsu\Auth::class);
		$auth->method('isAuthenticated')->willReturn(true);
		$this->container->setInstance('auth', $auth);

		$controller = new AnimeController($this->container);

		ob_start();
		$controller->addForm();
		ob_end_clean();

		$this->assertTrue(true);
	}

	public function testSearch(): void
	{
		$this->setSuperGlobals(['_GET' => ['query' => 'Test']]);

		$model = $this->createMock(\Aviat\AnimeClient\Model\Anime::class);
		$model
			->expects($this->once())
			->method('search')
			->with('Test')
			->willReturn([]);
		$this->container->setInstance('anime-model', $model);

		$controller = new AnimeController($this->container);

		ob_start();
		$controller->search();
		ob_end_clean();

		$this->assertTrue(true);
	}

	public function testRandom(): void
	{
		$model = $this->createMock(\Aviat\AnimeClient\Model\Anime::class);
		$model
			->expects($this->once())
			->method('getRandomAnime')
			->willReturn(\Aviat\AnimeClient\Types\Anime::from([
				'title' => 'Test',
				'titles' => ['Test'],
				'episode_count' => 12,
				'episode_length' => 24,
				'cover_image' => 'test.jpg',
				'show_type' => 'TV',
				'age_rating' => 'G',
				'genres' => [],
				'streaming_links' => [],
			]));
		$this->container->setInstance('anime-model', $model);

		$controller = new AnimeController($this->container);

		ob_start();
		$controller->random();
		ob_end_clean();

		$this->assertTrue(true);
	}

	public function testAdd(): void
	{
		$this->setSuperGlobals(['_POST' => ['id' => '123', 'mal_id' => '456']]);

		$auth = $this->createMock(\Aviat\AnimeClient\API\Kitsu\Auth::class);
		$auth->method('isAuthenticated')->willReturn(true);
		$this->container->setInstance('auth', $auth);

		$model = $this->createMock(\Aviat\AnimeClient\Model\Anime::class);
		$model
			->expects($this->once())
			->method('createItem')
			->willReturn(true);
		$this->container->setInstance('anime-model', $model);

		$controller = new AnimeController($this->container);

		ob_start();
		$controller->add();
		ob_end_clean();

		$this->assertTrue(true);
	}

	public function testDelete(): void
	{
		$this->setSuperGlobals(['_POST' => ['id' => '123']]);

		$auth = $this->createMock(\Aviat\AnimeClient\API\Kitsu\Auth::class);
		$auth->method('isAuthenticated')->willReturn(true);
		$this->container->setInstance('auth', $auth);

		$model = $this->createMock(\Aviat\AnimeClient\Model\Anime::class);
		$model
			->expects($this->once())
			->method('deleteItem')
			->willReturn(true);
		$this->container->setInstance('anime-model', $model);

		$controller = new AnimeController($this->container);

		ob_start();
		$controller->delete();
		ob_end_clean();

		$this->assertTrue(true);
	}

	public function testEdit(): void
	{
		$auth = $this->createMock(\Aviat\AnimeClient\API\Kitsu\Auth::class);
		$auth->method('isAuthenticated')->willReturn(true);
		$this->container->setInstance('auth', $auth);

		$model = $this->createMock(\Aviat\AnimeClient\Model\Anime::class);
		$model
			->expects($this->once())
			->method('getItem')
			->with('123')
			->willReturn(['id' => '123']);
		$this->container->setInstance('anime-model', $model);

		$controller = new AnimeController($this->container);

		ob_start();
		$controller->edit('123');
		ob_end_clean();

		$this->assertTrue(true);
	}

	public function testIncrement(): void
	{
		$this->setSuperGlobals([
			'_POST' => ['id' => '123'],
			'_SERVER' => array_merge($GLOBALS['_SERVER'], [
				'CONTENT_TYPE' => 'application/x-www-form-urlencoded',
			]),
		]);

		$auth = $this->createMock(\Aviat\AnimeClient\API\Kitsu\Auth::class);
		$auth->method('isAuthenticated')->willReturn(true);
		$this->container->setInstance('auth', $auth);

		$model = $this->createMock(\Aviat\AnimeClient\Model\Anime::class);
		$model
			->expects($this->once())
			->method('incrementItem')
			->willReturn(['body' => [], 'statusCode' => 200]);
		$this->container->setInstance('anime-model', $model);

		$controller = new AnimeController($this->container);

		ob_start();
		$controller->increment();
		ob_end_clean();

		$this->assertTrue(true);
	}
}
