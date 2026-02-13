<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\Controller;

use Aviat\AnimeClient\Controller\Manga as MangaController;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * @internal
 */
#[AllowMockObjectsWithoutExpectations]
final class MangaControllerTest extends AnimeClientTestCase
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

	public function testMangaList(): void
	{
		$model = $this->createMock(\Aviat\AnimeClient\Model\Manga::class);
		$model
			->expects($this->once())
			->method('getList')
			->willReturn([]);
		$this->container->setInstance('manga-model', $model);

		$controller = new MangaController($this->container);

		ob_start();
		$controller->index('reading', 'list');
		ob_end_clean();

		$this->assertTrue(true);
	}

	public function testDetails(): void
	{
		$model = $this->createMock(\Aviat\AnimeClient\Model\Manga::class);
		$model
			->expects($this->once())
			->method('getManga')
			->with('test-slug')
			->willReturn(\Aviat\AnimeClient\Types\MangaPage::from([
				'id' => '1',
				'title' => 'Test',
				'titles' => ['Test'],
				'titles_more' => [],
				'chapter_count' => 50,
				'volume_count' => 5,
				'cover_image' => 'test.jpg',
				'manga_type' => 'MANGA',
				'age_rating' => 'G',
				'age_rating_guide' => '',
				'genres' => [],
				'characters' => [],
				'staff' => [],
				'links' => [],
				'synopsis' => '',
				'url' => '',
			]));
		$this->container->setInstance('manga-model', $model);

		$controller = new MangaController($this->container);

		ob_start();
		$controller->details('test-slug');
		ob_end_clean();

		$this->assertTrue(true);
	}

	public function testMangaListAll(): void
	{
		$model = $this->createMock(\Aviat\AnimeClient\Model\Manga::class);
		$model
			->expects($this->once())
			->method('getList')
			->with('All')
			->willReturn([]);
		$this->container->setInstance('manga-model', $model);

		$controller = new MangaController($this->container);

		ob_start();
		$controller->index('all', 'list');
		ob_end_clean();

		$this->assertTrue(true);
	}

	public function testAdd(): void
	{
		$this->setSuperGlobals(['_POST' => ['id' => '123']]);

		$auth = $this->createMock(\Aviat\AnimeClient\API\Kitsu\Auth::class);
		$auth->method('isAuthenticated')->willReturn(true);
		$this->container->setInstance('auth', $auth);

		$model = $this->createMock(\Aviat\AnimeClient\Model\Manga::class);
		$model
			->expects($this->once())
			->method('createItem')
			->willReturn(true);
		$this->container->setInstance('manga-model', $model);

		$controller = new MangaController($this->container);

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

		$model = $this->createMock(\Aviat\AnimeClient\Model\Manga::class);
		$model
			->expects($this->once())
			->method('deleteItem')
			->willReturn(true);
		$this->container->setInstance('manga-model', $model);

		$controller = new MangaController($this->container);

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

		$model = $this->createMock(\Aviat\AnimeClient\Model\Manga::class);
		$model
			->expects($this->once())
			->method('getItem')
			->with('123')
			->willReturn(['id' => '123']);
		$this->container->setInstance('manga-model', $model);

		$controller = new MangaController($this->container);

		ob_start();
		$controller->edit('123');
		ob_end_clean();

		$this->assertTrue(true);
	}

	public function testSearch(): void
	{
		$this->setSuperGlobals(['_GET' => ['query' => 'Test']]);

		$model = $this->createMock(\Aviat\AnimeClient\Model\Manga::class);
		$model
			->expects($this->once())
			->method('search')
			->with('Test')
			->willReturn([]);
		$this->container->setInstance('manga-model', $model);

		$controller = new MangaController($this->container);

		ob_start();
		$controller->search();
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

		$model = $this->createMock(\Aviat\AnimeClient\Model\Manga::class);
		$model
			->expects($this->once())
			->method('incrementItem')
			->willReturn(['body' => [], 'statusCode' => 200]);
		$this->container->setInstance('manga-model', $model);

		$controller = new MangaController($this->container);

		ob_start();
		$controller->increment();
		ob_end_clean();

		$this->assertTrue(true);
	}

	public function testFormUpdate(): void
	{
		$this->setSuperGlobals(['_POST' => [
			'id' => '123',
			'mal_id' => '456',
			'status' => 'completed',
			'reread_count' => 0,
			'notes' => '',
			'chapters_read' => 50,
			'new_rating' => 8,
		]]);

		$auth = $this->createMock(\Aviat\AnimeClient\API\Kitsu\Auth::class);
		$auth->method('isAuthenticated')->willReturn(true);
		$this->container->setInstance('auth', $auth);

		$model = $this->createMock(\Aviat\AnimeClient\Model\Manga::class);
		$model
			->expects($this->once())
			->method('updateItem')
			->willReturn(['body' => [], 'statusCode' => 200]);
		$this->container->setInstance('manga-model', $model);

		$controller = new MangaController($this->container);

		ob_start();
		$controller->formUpdate();
		ob_end_clean();

		$this->assertTrue(true);
	}
}
