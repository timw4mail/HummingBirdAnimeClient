<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\Controller;

use Aviat\AnimeClient\Controller\Settings as SettingsController;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * @internal
 */
#[AllowMockObjectsWithoutExpectations]
final class SettingsControllerTest extends AnimeClientTestCase
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
		$auth = $this->createMock(\Aviat\AnimeClient\API\Kitsu\Auth::class);
		$auth->method('isAuthenticated')->willReturn(true);
		$this->container->setInstance('auth', $auth);

		$model = $this->createMock(\Aviat\AnimeClient\Model\Settings::class);
		$model
			->expects($this->once())
			->method('getSettingsForm')
			->willReturn([]);
		$this->container->setInstance('settings-model', $model);

		$controller = new SettingsController($this->container);

		ob_start();
		$controller->index();
		ob_end_clean();

		$this->assertTrue(true);
	}
}
