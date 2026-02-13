<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.4
 *
 * @copyright   2015 - 2026  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.3
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests\API\Kitsu;

use Aviat\AnimeClient\API\Kitsu\Model;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;

/**
 * @internal
 */
final class ModelTest extends AnimeClientTestCase
{
	protected $model;

	protected $requestBuilder;

	#[\Override]
	protected function setUp(): void
	{
		parent::setup();

		$this->requestBuilder = $this->createMock(\Aviat\AnimeClient\API\Kitsu\RequestBuilder::class);

		$this->model = $this->container->get('kitsu-model');
		$this->model->setRequestBuilder($this->requestBuilder);
	}

	public function testGetAnimeKitsuIdFromMALId(): void
	{
		$this->requestBuilder
			->expects($this->once())
			->method('runQuery')
			->with('GetIdByMapping', [
				'id' => '1',
				'site' => 'MYANIMELIST_ANIME',
			])
			->willReturn(['data' => ['lookupMapping' => ['id' => '1']]]);

		$kitsuId = $this->model->getKitsuIdFromMALId('1', 'anime');
		$this->assertSame('1', $kitsuId);
	}

	public function testGetUserIdByUsername(): void
	{
		$this->requestBuilder
			->expects($this->once())
			->method('runQuery')
			->with('GetUserId', ['slug' => 'test_user'])
			->willReturn(['data' => ['findProfileBySlug' => ['id' => '123']]]);

		$userId = $this->model->getUserIdByUsername('test_user');
		$this->assertEquals('123', $userId);
	}

	public function testGetUserData(): void
	{
		$this->requestBuilder
			->expects($this->once())
			->method('runQuery')
			->with('UserDetails', ['slug' => 'test_user'])
			->willReturn(['data' => ['findProfileBySlug' => ['id' => '123']]]);

		$userData = $this->model->getUserData('test_user');
		$this->assertEquals('123', $userData['data']['findProfileBySlug']['id']);
	}

	public function testGetNullFromMALAnimeId(): void
	{
		$this->requestBuilder
			->expects($this->once())
			->method('runQuery')
			->willReturn([]);

		$kitsuId = $this->model->getKitsuIdFromMALId('0', 'anime');
		$this->assertNull($kitsuId);
	}
}
