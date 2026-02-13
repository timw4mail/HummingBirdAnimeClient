<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\API\Kitsu;

use Aviat\AnimeClient\API\Kitsu\Auth;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;

final class AuthTest extends AnimeClientTestCase
{
	protected Auth $auth;

	protected $kitsuModel;

	#[\Override]
	protected function setUp(): void
	{
		parent::setUp();

		$this->container->get('config')->set('kitsu_username', 'test_user');
		$this->container->get('cache')->clear();
		$this->container
			->get('session')
			->getSegment(\Aviat\AnimeClient\SESSION_SEGMENT)
			->clear();

		$this->kitsuModel = $this->createMock(\Aviat\AnimeClient\API\Kitsu\Model::class);
		$this->container->setInstance('kitsu-model', $this->kitsuModel);

		$this->auth = new Auth($this->container);
	}

	public function testAuthenticateSuccess(): void
	{
		$this->kitsuModel
			->expects($this->once())
			->method('authenticate')
			->willReturn([
				'access_token' => 'test_access_token',
				'refresh_token' => 'test_refresh_token',
				'expires_in' => 3600,
				'created_at' => time(),
			]);

		$this->assertTrue($this->auth->authenticate('password'));
		$this->assertTrue($this->auth->isAuthenticated());
		$this->assertEquals('test_access_token', $this->auth->getAuthToken());
	}

	public function testAuthenticateFailure(): void
	{
		$this->kitsuModel
			->expects($this->once())
			->method('authenticate')
			->willReturn(false);

		$this->assertFalse($this->auth->authenticate('password'));
		$this->assertFalse($this->auth->isAuthenticated());
	}

	public function testLogout(): void
	{
		$this->kitsuModel
			->expects($this->once())
			->method('authenticate')
			->willReturn([
				'access_token' => 'test_access_token',
				'refresh_token' => 'test_refresh_token',
				'expires_in' => 3600,
				'created_at' => time(),
			]);

		$this->auth->authenticate('password');
		$this->assertTrue($this->auth->isAuthenticated());

		$this->auth->logout();
		$this->container->get('cache')->clear();
		$this->assertFalse($this->auth->isAuthenticated());
	}
}
