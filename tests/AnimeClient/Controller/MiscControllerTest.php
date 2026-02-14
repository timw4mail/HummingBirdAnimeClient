<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\Controller;

use Aviat\AnimeClient\Controller\Misc as MiscController;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * @internal
 */
#[AllowMockObjectsWithoutExpectations]
final class MiscControllerTest extends AnimeClientTestCase
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
		$this->container->get('config')->set('default_list', 'anime');
		$this->container->get('config')->set('default_anime_list_path', 'watching');
		$controller = new MiscController($this->container);

		ob_start();
		$controller->index();
		ob_end_clean();

		$this->assertTrue(true);
	}

	public function testLogin(): void
	{
		$controller = new MiscController($this->container);

		ob_start();
		$controller->login();
		ob_end_clean();

		$this->assertTrue(true);
	}

	public function testLogout(): void
	{
		$this->container->get('config')->set('default_list', 'anime');
		$this->container->get('config')->set('default_anime_list_path', 'watching');
		$auth = $this->createMock(\Aviat\AnimeClient\API\Kitsu\Auth::class);
		$auth->expects($this->once())->method('logout');
		$this->container->setInstance('auth', $auth);

		$controller = new MiscController($this->container);

		ob_start();
		$controller->logout();
		ob_end_clean();

		$this->assertTrue(true);
	}

	public function testHeartbeat(): void
	{
		$auth = $this->createMock(\Aviat\AnimeClient\API\Kitsu\Auth::class);
		$auth->method('isAuthenticated')->willReturn(true);
		$this->container->setInstance('auth', $auth);

		$controller = new MiscController($this->container);

		ob_start();
		$controller->heartbeat();
		ob_end_clean();

		$this->assertTrue(true);
	}

	public function testClearCache(): void
	{
		$auth = $this->createMock(\Aviat\AnimeClient\API\Kitsu\Auth::class);
		$auth->method('isAuthenticated')->willReturn(true);
		$this->container->setInstance('auth', $auth);

		$controller = new MiscController($this->container);

		ob_start();
		$controller->clearCache();
		ob_end_clean();

		$this->assertTrue(true);
	}

	public function testLoginAction(): void
	{
		$this->setSuperGlobals(['_POST' => ['password' => 'pass']]);

		$auth = $this->createMock(\Aviat\AnimeClient\API\Kitsu\Auth::class);
		$auth
			->expects($this->once())
			->method('authenticate')
			->with('pass')
			->willReturn(true);
		$this->container->setInstance('auth', $auth);

		$controller = new MiscController($this->container);

		ob_start();
		$controller->loginAction();
		ob_end_clean();

		$this->assertTrue(true);
	}

	public function testLoginActionFailure(): void
	{
		$this->setSuperGlobals(['_POST' => ['password' => 'wrong']]);

		$auth = $this->createMock(\Aviat\AnimeClient\API\Kitsu\Auth::class);
		$auth
			->expects($this->once())
			->method('authenticate')
			->willReturn(false);
		$this->container->setInstance('auth', $auth);

		$controller = new MiscController($this->container);

		ob_start();
		$controller->loginAction();
		ob_end_clean();

		$this->assertTrue(true);
	}

	public function testLoginWithStatus(): void
	{
		$controller = new MiscController($this->container);

		ob_start();
		$controller->login('fail');
		ob_end_clean();

		$this->assertTrue(true);
	}

	public function testHeartbeatUnauthenticated(): void
	{
		$auth = $this->createMock(\Aviat\AnimeClient\API\Kitsu\Auth::class);
		$auth->method('isAuthenticated')->willReturn(false);
		$this->container->setInstance('auth', $auth);

		$controller = new MiscController($this->container);

		ob_start();
		$controller->heartbeat();
		ob_end_clean();

		$this->assertTrue(true);
	}

	public function testCharacter(): void
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

		$controller = new MiscController($this->container);

		ob_start();
		$controller->character('test-slug');
		ob_end_clean();

		$this->assertTrue(true);
	}

	public function testPerson(): void
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

		$controller = new MiscController($this->container);

		ob_start();
		$controller->person('test-slug');
		ob_end_clean();

		$this->assertTrue(true);
	}
}
