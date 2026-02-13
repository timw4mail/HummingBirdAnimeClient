<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\Controller;

use Aviat\AnimeClient\API\Kitsu\Model;
use Aviat\AnimeClient\Controller\User as UserController;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * @internal
 */
#[AllowMockObjectsWithoutExpectations]
final class UserControllerTest extends AnimeClientTestCase
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

	public function testMe(): void
	{
		$this->container->get('config')->set('kitsu_username', 'test_user');
		$this->container->get('config')->set('whose_list', 'Test');

		$model = $this->createMock(Model::class);
		$model
			->expects($this->once())
			->method('getUserData')
			->with('test_user')
			->willReturn(['data' => ['findProfileBySlug' => [
				'id' => '1',
				'slug' => 'test_user',
				'name' => 'Test',
				'avatarImage' => ['original' => ['url' => 'test.jpg']],
				'bannerImage' => ['original' => ['url' => 'banner.jpg']],
				'about' => 'Test',
				'birthday' => '2000-01-01',
				'createdAt' => '2015-01-01',
				'gender' => 'Other',
				'location' => 'Earth',
				'favorites' => ['nodes' => []],
				'stats' => [],
				'waifu' => null,
				'siteLinks' => ['nodes' => []],
			]]]);
		$this->container->setInstance('kitsu-model', $model);

		$controller = new UserController($this->container);

		ob_start();
		$controller->me();
		ob_end_clean();

		$this->assertTrue(true);
	}
}
