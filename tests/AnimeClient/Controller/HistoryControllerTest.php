<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\Controller;

use Aviat\AnimeClient\Controller\History as HistoryController;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * @internal
 */
#[AllowMockObjectsWithoutExpectations]
final class HistoryControllerTest extends AnimeClientTestCase
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

	public function testAnimeHistory(): void
	{
		$model = $this->createMock(\Aviat\AnimeClient\Model\Anime::class);
		$model
			->expects($this->once())
			->method('getHistory')
			->willReturn([]);
		$this->container->setInstance('anime-model', $model);

		$controller = new HistoryController($this->container);

		ob_start();
		$controller->index('anime');
		ob_end_clean();

		$this->assertTrue(true);
	}

	public function testMangaHistory(): void
	{
		$model = $this->createMock(\Aviat\AnimeClient\Model\Manga::class);
		$model
			->expects($this->once())
			->method('getHistory')
			->willReturn([]);
		$this->container->setInstance('manga-model', $model);

		$controller = new HistoryController($this->container);

		ob_start();
		$controller->index('manga');
		ob_end_clean();

		$this->assertTrue(true);
	}
}
