<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\Controller;

use Aviat\AnimeClient\Controller\AnimeCollection as AnimeCollectionController;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * @internal
 */
#[AllowMockObjectsWithoutExpectations]
final class AnimeCollectionControllerTest extends AnimeClientTestCase
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

	public function testView(): void
	{
		$model = $this->createMock(\Aviat\AnimeClient\Model\AnimeCollection::class);
		$model
			->expects($this->once())
			->method('getCollection')
			->willReturn([]);
		$model
			->expects($this->once())
			->method('getFlatCollection')
			->willReturn([]);
		$this->container->setInstance('anime-collection-model', $model);

		$controller = new AnimeCollectionController($this->container);

		ob_start();
		$controller->view();
		ob_end_clean();

		$this->assertTrue(true);
	}

	public function testForm(): void
	{
		$auth = $this->createMock(\Aviat\AnimeClient\API\Kitsu\Auth::class);
		$auth->method('isAuthenticated')->willReturn(true);
		$this->container->setInstance('auth', $auth);

		$model = $this->createMock(\Aviat\AnimeClient\Model\AnimeCollection::class);
		$model->method('getMediaTypeList')->willReturn([]);
		$this->container->setInstance('anime-collection-model', $model);

		$controller = new AnimeCollectionController($this->container);

		ob_start();
		$controller->form();
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
			->willReturn([]);
		$this->container->setInstance('anime-model', $model);

		$controller = new AnimeCollectionController($this->container);

		ob_start();
		$controller->search();
		ob_end_clean();

		$this->assertTrue(true);
	}

	public function testAdd(): void
	{
		$this->setSuperGlobals(['_POST' => ['id' => '123', 'notes' => 'Test']]);

		$auth = $this->createMock(\Aviat\AnimeClient\API\Kitsu\Auth::class);
		$auth->method('isAuthenticated')->willReturn(true);
		$this->container->setInstance('auth', $auth);

		$model = $this->createMock(\Aviat\AnimeClient\Model\AnimeCollection::class);
		$model->expects($this->once())->method('has')->willReturn(false);
		$model->expects($this->once())->method('add');
		$model->expects($this->once())->method('wasAdded')->willReturn(true);
		$this->container->setInstance('anime-collection-model', $model);

		$controller = new AnimeCollectionController($this->container);

		ob_start();
		$controller->add();
		ob_end_clean();

		$this->assertTrue(true);
	}

	public function testDelete(): void
	{
		$this->setSuperGlobals(['_POST' => ['hummingbird_id' => '123']]);

		$auth = $this->createMock(\Aviat\AnimeClient\API\Kitsu\Auth::class);
		$auth->method('isAuthenticated')->willReturn(true);
		$this->container->setInstance('auth', $auth);

		$model = $this->createMock(\Aviat\AnimeClient\Model\AnimeCollection::class);
		$model->expects($this->once())->method('delete');
		$model->expects($this->once())->method('wasDeleted')->willReturn(true);
		$this->container->setInstance('anime-collection-model', $model);

		$controller = new AnimeCollectionController($this->container);

		ob_start();
		$controller->delete();
		ob_end_clean();

		$this->assertTrue(true);
	}
}
