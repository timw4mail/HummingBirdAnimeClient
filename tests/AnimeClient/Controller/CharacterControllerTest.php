<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\Controller;

use Aviat\AnimeClient\Controller\Character as CharacterController;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * @internal
 */
#[AllowMockObjectsWithoutExpectations]
final class CharacterControllerTest extends AnimeClientTestCase
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
			->method('getCharacter')
			->with('test-slug')
			->willReturn(['data' => ['findCharacterBySlug' => [
				'id' => '1',
				'slug' => 'test-slug',
				'names' => [
					'canonical' => 'Test',
					'localized' => [],
					'alternatives' => [],
				],
				'image' => ['original' => ['url' => 'test.jpg']],
				'description' => ['en' => 'Test'],
				'media' => ['nodes' => []],
			]]]);
		$this->container->setInstance('kitsu-model', $model);

		$controller = new CharacterController($this->container);

		ob_start();
		$controller->index('test-slug');
		ob_end_clean();

		$this->assertTrue(true);
	}
}
