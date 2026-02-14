<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\Controller;

use Aviat\AnimeClient\Controller\People as PeopleController;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

class MockPeopleController extends PeopleController
{
	public function notFound(
		string $title = 'Sorry, page not found',
		string $message = 'Page Not Found',
	): never {
		throw new \RuntimeException('Bypass exit');
	}
}

/**
 * @internal
 */
#[AllowMockObjectsWithoutExpectations]
final class PeopleControllerTest extends AnimeClientTestCase
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

	public function testIndex(): void
	{
		$model = $this->createMock(\Aviat\AnimeClient\API\Kitsu\Model::class);
		$model
			->expects($this->once())
			->method('getPerson')
			->with('test-slug')
			->willReturn(['data' => ['findPersonBySlug' => [
				'id' => '1',
				'slug' => 'test-slug',
				'names' => [
					'canonical' => 'Test',
					'localized' => ['Test' => 'Test Person'],
				],
				'image' => ['original' => ['url' => 'test.jpg']],
				'birthday' => '1990-01-01',
				'description' => ['en' => 'Test'],
				'mediaStaff' => ['nodes' => []],
				'voices' => ['nodes' => []],
			]]]);
		$this->container->setInstance('kitsu-model', $model);

		$controller = new PeopleController($this->container);

		ob_start();
		$controller->index('test-slug');
		ob_end_clean();

		$this->assertTrue(true);
	}

	public function testIndexNotFound(): void
	{
		$model = $this->createMock(\Aviat\AnimeClient\API\Kitsu\Model::class);
		$model
			->expects($this->once())
			->method('getPerson')
			->with('missing-slug')
			->willReturn(['data' => ['findPersonBySlug' => null]]);
		$this->container->setInstance('kitsu-model', $model);

		$controller = new MockPeopleController($this->container);

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Bypass exit');

		$controller->index('missing-slug');
	}
}
