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
use Aviat\AnimeClient\Kitsu as K;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * @internal
 */
#[AllowMockObjectsWithoutExpectations]
final class ModelTest extends AnimeClientTestCase
{
	protected $model;

	protected $requestBuilder;

	#[\Override]
	protected function setUp(): void
	{
		parent::setUp();

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

	public function testGetCharacter(): void
	{
		$this->requestBuilder
			->expects($this->once())
			->method('runQuery')
			->with('CharacterDetails', ['slug' => 'test-slug'])
			->willReturn(['data' => []]);

		$result = $this->model->getCharacter('test-slug');
		$this->assertEquals(['data' => []], $result);
	}

	public function testGetPerson(): void
	{
		$this->requestBuilder
			->expects($this->once())
			->method('runQuery')
			->with('PersonDetails', ['slug' => 'test-slug'])
			->willReturn(['data' => []]);

		$result = $this->model->getPerson('test-slug');
		$this->assertEquals(['data' => []], $result);
	}

	public function testGetAnime(): void
	{
		$this->requestBuilder
			->expects($this->once())
			->method('runQuery')
			->with('AnimeDetails', ['slug' => 'test-slug'])
			->willReturn(['data' => ['findAnimeBySlug' => [
				'id' => '1',
				'slug' => 'test-slug',
				'titles' => ['canonical' => 'Test', 'localized' => []],
				'categories' => ['nodes' => []],
				'characters' => ['nodes' => []],
				'staff' => ['nodes' => []],
				'mappings' => ['nodes' => []],
				'startDate' => '2020-01-01',
				'endDate' => '2020-12-31',
				'ageRating' => 'G',
				'ageRatingGuide' => '',
				'episodeCount' => 12,
				'episodeLength' => 24,
				'subtype' => 'TV',
				'youtubeTrailerVideoId' => '',
				'totalLength' => 0,
				'streamingLinks' => ['nodes' => []],
			]]]);

		$result = $this->model->getAnime('test-slug');
		$this->assertInstanceOf(\Aviat\AnimeClient\Types\Anime::class, $result);
		$this->assertEquals('1', $result['id']);
	}

	public function testGetAnimeById(): void
	{
		$this->requestBuilder
			->expects($this->once())
			->method('runQuery')
			->with('AnimeDetailsById', ['id' => '1'])
			->willReturn(['data' => ['findAnimeById' => [
				'id' => '1',
				'slug' => 'test-slug',
				'titles' => ['canonical' => 'Test', 'localized' => []],
				'categories' => ['nodes' => []],
				'characters' => ['nodes' => []],
				'staff' => ['nodes' => []],
				'mappings' => ['nodes' => []],
				'startDate' => '2020-01-01',
				'endDate' => '2020-12-31',
				'ageRating' => 'G',
				'ageRatingGuide' => '',
				'episodeCount' => 12,
				'episodeLength' => 24,
				'subtype' => 'TV',
				'youtubeTrailerVideoId' => '',
				'totalLength' => 0,
				'streamingLinks' => ['nodes' => []],
			]]]);

		$result = $this->model->getAnimeById('1');
		$this->assertInstanceOf(\Aviat\AnimeClient\Types\Anime::class, $result);
	}

	public function testGetAnimeHistory(): void
	{
		$cache = $this->createMock(\Psr\SimpleCache\CacheInterface::class);
		$cache
			->expects($this->once())
			->method('get')
			->with(\Aviat\AnimeClient\Kitsu::ANIME_HISTORY_LIST_CACHE_KEY)
			->willReturn(null);

		$this->requestBuilder
			->expects($this->once())
			->method('runQuery')
			->with('GetUserHistory', ['slug' => 'test_user'])
			->willReturn(['data' => ['findProfileBySlug' => ['libraryEvents' => ['nodes' => []]]]]);

		$this->model->setCache($cache);
		$this->container->get('config')->set('kitsu_username', 'test_user');

		$result = $this->model->getAnimeHistory();
		$this->assertEquals([], $result);
	}

	public function testAuthenticate(): void
	{
		$response = $this->createMock(\Amp\Http\Client\Response::class);
		$body = new \Amp\ByteStream\Payload(json_encode(['access_token' => 'foo']));
		$response
			->expects($this->once())
			->method('getBody')
			->willReturn($body);

		$this->requestBuilder
			->expects($this->once())
			->method('getResponse')
			->willReturn($response);

		$result = $this->model->authenticate('user', 'pass');
		$this->assertEquals(['access_token' => 'foo'], $result);
	}

	public function testReAuthenticate(): void
	{
		$response = $this->createMock(\Amp\Http\Client\Response::class);
		$body = new \Amp\ByteStream\Payload(json_encode(['access_token' => 'new_foo']));
		$response->method('getStatus')->willReturn(200);
		$response->method('getBody')->willReturn($body);

		$this->requestBuilder
			->expects($this->once())
			->method('getResponse')
			->with('POST', K::AUTH_URL, $this->callback(function ($options) {
				return $options['form_params']['grant_type'] === 'refresh_token'
					&& $options['form_params']['refresh_token'] === 'old_token';
			}))
			->willReturn($response);

		$result = $this->model->reAuthenticate('old_token');
		$this->assertEquals(['access_token' => 'new_foo'], $result);
	}

	public function testGetRandomAnime(): void
	{
		$this->requestBuilder
			->expects($this->once())
			->method('runQuery')
			->with('RandomMedia', ['type' => 'ANIME'])
			->willReturn(['data' => ['randomMedia' => [
				'id' => '1',
				'slug' => 'test-slug',
				'titles' => ['canonical' => 'Test', 'localized' => []],
				'categories' => ['nodes' => []],
				'characters' => ['nodes' => []],
				'staff' => ['nodes' => []],
				'mappings' => ['nodes' => []],
				'startDate' => '2020-01-01',
				'endDate' => '2020-12-31',
				'ageRating' => 'G',
				'ageRatingGuide' => '',
				'episodeCount' => 12,
				'episodeLength' => 24,
				'subtype' => 'TV',
				'youtubeTrailerVideoId' => '',
				'totalLength' => 0,
				'streamingLinks' => ['nodes' => []],
			]]]);

		$result = $this->model->getRandomAnime();
		$this->assertInstanceOf(\Aviat\AnimeClient\Types\Anime::class, $result);
	}

	public function testSearch(): void
	{
		$this->requestBuilder
			->expects($this->once())
			->method('runQuery')
			->with('SearchAnime', ['query' => 'Test'])
			->willReturn(['data' => ['searchAnimeByTitle' => ['nodes' => [
				[
					'id' => '1',
					'slug' => 'test',
					'posterImage' => ['original' => ['url' => 'test.jpg']],
					'titles' => ['canonical' => 'Test', 'localized' => []],
					'myLibraryEntry' => null,
					'mappings' => ['nodes' => []],
				],
			]]]]);

		$result = $this->model->search('anime', 'Test');
		$this->assertCount(1, $result);
		$this->assertEquals('1', $result[0]['id']);
	}

	public function testGetFullOrganizedAnimeList(): void
	{
		$client = $this->createMock(\Amp\Http\Client\HttpClient::class);

		$jsonData = [
			'data' => [
				'findProfileBySlug' => [
					'library' => [
						'all' => [
							'nodes' => [
								[
									'id' => '1',
									'status' => 'CURRENT',
									'progress' => 5,
									'rating' => 16,
									'reconsuming' => false,
									'reconsumeCount' => 0,
									'notes' => '',
									'media' => [
										'id' => '10',
										'slug' => 'test-anime',
										'subtype' => 'TV',
										'episodeCount' => 12,
										'episodeLength' => 24,
										'startDate' => '2020-01-01',
										'endDate' => '2020-12-31',
										'ageRating' => 'G',
										'titles' => ['canonical' => 'Test', 'localized' => []],
										'mappings' => ['nodes' => []],
										'streamingLinks' => ['nodes' => []],
										'posterImage' => ['original' => ['url' => 'test.jpg']],
									],
								],
							],
							'pageInfo' => [
								'endCursor' => '',
								'hasNextPage' => false,
							],
						],
					],
				],
			],
		];

		$client
			->method('request')
			->willReturnCallback(function () use ($jsonData) {
				$response = $this->createMock(\Amp\Http\Client\Response::class);
				$body = new \Amp\ByteStream\Payload(json_encode($jsonData));
				$response->method('getBody')->willReturn($body);

				return $response;
			});

		\Aviat\AnimeClient\getApiClient($client);

		$this->requestBuilder
			->method('queryRequest')
			->willReturn(new \Amp\Http\Client\Request('https://example.com'));

		$cache = $this->createMock(\Psr\SimpleCache\CacheInterface::class);
		$cache->method('get')->willReturn(null);
		$this->model->setCache($cache);

		$this->container->get('config')->set('kitsu_username', 'test_user');

		$result = $this->model->getFullOrganizedAnimeList();
		$this->assertArrayHasKey('Currently Watching', $result);
		$this->assertArrayHasKey('1', $result['Currently Watching']);
	}

	public function testGetManga(): void
	{
		$this->requestBuilder
			->expects($this->once())
			->method('runQuery')
			->with('MangaDetails', ['slug' => 'test-slug'])
			->willReturn(['data' => ['findMangaBySlug' => [
				'id' => '1',
				'slug' => 'test-slug',
				'titles' => ['canonical' => 'Test', 'localized' => []],
				'categories' => ['nodes' => []],
				'characters' => ['nodes' => []],
				'staff' => ['nodes' => []],
				'mappings' => ['nodes' => []],
				'startDate' => '2020-01-01',
				'endDate' => '2020-12-31',
				'ageRating' => 'G',
				'ageRatingGuide' => '',
				'chapterCount' => 50,
				'volumeCount' => 5,
				'subtype' => 'MANGA',
				'description' => ['en' => 'Test'],
			]]]);

		$result = $this->model->getManga('test-slug');
		$this->assertInstanceOf(\Aviat\AnimeClient\Types\MangaPage::class, $result);
		$this->assertEquals('1', $result['id']);
	}

	public function testGetMangaById(): void
	{
		$this->requestBuilder
			->expects($this->once())
			->method('runQuery')
			->with('MangaDetailsById', ['id' => '1'])
			->willReturn(['data' => ['findMangaById' => [
				'id' => '1',
				'slug' => 'test-slug',
				'titles' => ['canonical' => 'Test', 'localized' => []],
				'categories' => ['nodes' => []],
				'characters' => ['nodes' => []],
				'staff' => ['nodes' => []],
				'mappings' => ['nodes' => []],
				'startDate' => '2020-01-01',
				'endDate' => '2020-12-31',
				'ageRating' => 'G',
				'ageRatingGuide' => '',
				'chapterCount' => 50,
				'volumeCount' => 5,
				'subtype' => 'MANGA',
				'description' => ['en' => 'Test'],
			]]]);

		$result = $this->model->getMangaById('1');
		$this->assertInstanceOf(\Aviat\AnimeClient\Types\MangaPage::class, $result);
	}

	public function testGetRandomManga(): void
	{
		$this->requestBuilder
			->expects($this->once())
			->method('runQuery')
			->with('RandomMedia', ['type' => 'MANGA'])
			->willReturn(['data' => ['randomMedia' => [
				'id' => '1',
				'slug' => 'test-slug',
				'titles' => ['canonical' => 'Test', 'localized' => []],
				'categories' => ['nodes' => []],
				'characters' => ['nodes' => []],
				'staff' => ['nodes' => []],
				'mappings' => ['nodes' => []],
				'startDate' => '2020-01-01',
				'endDate' => '2020-12-31',
				'ageRating' => 'G',
				'ageRatingGuide' => '',
				'chapterCount' => 50,
				'volumeCount' => 5,
				'subtype' => 'MANGA',
				'description' => ['en' => 'Test'],
			]]]);

		$result = $this->model->getRandomManga();
		$this->assertInstanceOf(\Aviat\AnimeClient\Types\MangaPage::class, $result);
	}

	public function testGetMangaHistory(): void
	{
		$cache = $this->createMock(\Psr\SimpleCache\CacheInterface::class);
		$cache
			->method('get')
			->willReturn(null);
		$this->model->setCache($cache);
		$this->container->get('config')->set('kitsu_username', 'test_user');

		$this->requestBuilder
			->expects($this->once())
			->method('runQuery')
			->willReturn(['data' => ['findProfileBySlug' => ['libraryEvents' => ['nodes' => []]]]]);

		$result = $this->model->getMangaHistory();
		$this->assertEquals([], $result);
	}

	public function testGetFullOrganizedMangaList(): void
	{
		$client = $this->createMock(\Amp\Http\Client\HttpClient::class);

		$jsonData = [
			'data' => [
				'findProfileBySlug' => [
					'library' => [
						'all' => [
							'nodes' => [
								[
									'id' => '1',
									'status' => 'CURRENT',
									'progress' => 5,
									'rating' => 16,
									'reconsuming' => false,
									'reconsumeCount' => 0,
									'notes' => '',
									'media' => [
										'id' => '10',
										'slug' => 'test-manga',
										'subtype' => 'MANGA',
										'chapterCount' => 12,
										'volumeCount' => 2,
										'episodeLength' => 24,
										'startDate' => '2020-01-01',
										'endDate' => '2020-12-31',
										'ageRating' => 'G',
										'titles' => ['canonical' => 'Test', 'localized' => []],
										'mappings' => ['nodes' => []],
										'streamingLinks' => ['nodes' => []],
										'posterImage' => ['original' => ['url' => 'test.jpg']],
									],
								],
							],
							'pageInfo' => [
								'endCursor' => '',
								'hasNextPage' => false,
							],
						],
					],
				],
			],
		];

		$client
			->method('request')
			->willReturnCallback(function () use ($jsonData) {
				$response = $this->createMock(\Amp\Http\Client\Response::class);
				$body = new \Amp\ByteStream\Payload(json_encode($jsonData));
				$response->method('getBody')->willReturn($body);

				return $response;
			});

		\Aviat\AnimeClient\getApiClient($client);

		$this->requestBuilder
			->method('queryRequest')
			->willReturn(new \Amp\Http\Client\Request('https://example.com'));

		$cache = $this->createMock(\Psr\SimpleCache\CacheInterface::class);
		$cache->method('get')->willReturn(null);
		$this->model->setCache($cache);

		$this->container->get('config')->set('kitsu_username', 'test_user');

		$result = $this->model->getFullOrganizedMangaList();
		$this->assertArrayHasKey('Currently Reading', $result);
		$this->assertArrayHasKey('1', $result['Currently Reading']);
	}

	public function testGetListItem(): void
	{
		$listItem = $this->createMock(\Aviat\AnimeClient\API\Kitsu\ListItem::class);
		$listItem
			->expects($this->once())
			->method('get')
			->with('123')
			->willReturn(['data' => ['findLibraryEntryById' => [
				'id' => '123',
				'status' => 'CURRENT',
				'progress' => 5,
				'rating' => 16,
				'reconsuming' => false,
				'reconsumeCount' => 0,
				'notes' => '',
				'media' => [
					'id' => '10',
					'slug' => 'test-anime',
					'subtype' => 'TV',
					'episodeCount' => 12,
					'episodeLength' => 24,
					'startDate' => '2020-01-01',
					'endDate' => '2020-12-31',
					'ageRating' => 'G',
					'titles' => ['canonical' => 'Test', 'localized' => []],
					'mappings' => ['nodes' => []],
					'streamingLinks' => ['nodes' => []],
					'posterImage' => ['original' => ['url' => 'test.jpg']],
				],
			]]]);

		$model = new Model($listItem);
		$result = $model->getListItem('123');
		$this->assertInstanceOf(\Aviat\AnimeClient\Types\AnimeListItem::class, $result);
	}

	public function testGetAnimeListCount(): void
	{
		$this->requestBuilder
			->expects($this->once())
			->method('runQuery')
			->with('GetLibraryCount', [
				'type' => 'ANIME',
				'slug' => 'test_user',
			])
			->willReturn(['data' => ['findProfileBySlug' => ['library' => ['all' => [
				'totalCount' => 42,
			]]]]]);

		$this->container->get('config')->set('kitsu_username', 'test_user');
		$result = $this->model->getAnimeListCount();
		$this->assertEquals(42, $result);
	}

	public function testGetAnimeListCountWithStatus(): void
	{
		$this->requestBuilder
			->expects($this->once())
			->method('runQuery')
			->with('GetLibraryCount', [
				'type' => 'ANIME',
				'slug' => 'test_user',
				'status' => 'CURRENT',
			])
			->willReturn(['data' => ['findProfileBySlug' => ['library' => ['all' => [
				'totalCount' => 10,
			]]]]]);

		$this->container->get('config')->set('kitsu_username', 'test_user');
		$result = $this->model->getAnimeListCount('current');
		$this->assertEquals(10, $result);
	}

	public function testGetRandomLibraryAnime(): void
	{
		$result = $this->model->getRandomLibraryAnime('current');
		$this->assertInstanceOf(\Aviat\AnimeClient\Types\Anime::class, $result);
		$this->assertTrue($result->isEmpty());
	}

	public function testGetMangaListCount(): void
	{
		$this->requestBuilder
			->expects($this->once())
			->method('runQuery')
			->with('GetLibraryCount', [
				'type' => 'MANGA',
				'slug' => 'test_user',
			])
			->willReturn(['data' => ['findProfileBySlug' => ['library' => ['all' => [
				'totalCount' => 24,
			]]]]]);

		$this->container->get('config')->set('kitsu_username', 'test_user');
		$result = $this->model->getMangaListCount();
		$this->assertEquals(24, $result);
	}

	public function testGetThumbList(): void
	{
		$client = $this->createMock(\Amp\Http\Client\HttpClient::class);
		$jsonData = [
			'data' => [
				'findProfileBySlug' => [
					'library' => [
						'all' => [
							'nodes' => [['id' => '1']],
							'pageInfo' => ['endCursor' => '', 'hasNextPage' => false],
						],
					],
				],
			],
		];

		$client
			->method('request')
			->willReturnCallback(function () use ($jsonData) {
				$response = $this->createMock(\Amp\Http\Client\Response::class);
				$body = new \Amp\ByteStream\Payload(json_encode($jsonData));
				$response->method('getBody')->willReturn($body);

				return $response;
			});

		\Aviat\AnimeClient\getApiClient($client);
		$this->requestBuilder
			->method('queryRequest')
			->willReturn(new \Amp\Http\Client\Request('https://example.com'));
		$this->container->get('config')->set('kitsu_username', 'test_user');

		$result = $this->model->getThumbList('anime');
		$this->assertCount(5, $result); // 5 statuses
	}

	public function testGetListPagesPagination(): void
	{
		$client = $this->createMock(\Amp\Http\Client\HttpClient::class);

		$media = [
			'id' => '10',
			'slug' => 'test-anime',
			'subtype' => 'TV',
			'episodeCount' => 12,
			'episodeLength' => 24,
			'startDate' => '2020-01-01',
			'endDate' => '2020-12-31',
			'ageRating' => 'G',
			'titles' => ['canonical' => 'Test', 'localized' => []],
			'mappings' => ['nodes' => []],
			'streamingLinks' => ['nodes' => []],
			'posterImage' => ['original' => ['url' => 'test.jpg']],
		];

		$jsonData1 = [
			'data' => [
				'findProfileBySlug' => [
					'library' => [
						'all' => [
							'nodes' => [
								[
									'id' => '1',
									'status' => 'CURRENT',
									'progress' => 5,
									'rating' => 16,
									'reconsuming' => false,
									'reconsumeCount' => 0,
									'notes' => '',
									'media' => $media,
								],
							],
							'pageInfo' => ['endCursor' => 'next', 'hasNextPage' => true],
						],
					],
				],
			],
		];
		$jsonData2 = [
			'data' => [
				'findProfileBySlug' => [
					'library' => [
						'all' => [
							'nodes' => [
								[
									'id' => '2',
									'status' => 'CURRENT',
									'progress' => 6,
									'rating' => 16,
									'reconsuming' => false,
									'reconsumeCount' => 0,
									'notes' => '',
									'media' => $media,
								],
							],
							'pageInfo' => ['endCursor' => '', 'hasNextPage' => false],
						],
					],
				],
			],
		];

		$callCount = 0;
		$client
			->method('request')
			->willReturnCallback(function () use (&$callCount, $jsonData1, $jsonData2) {
				$data = $callCount === 0 ? $jsonData1 : $jsonData2;
				$callCount++;
				$response = $this->createMock(\Amp\Http\Client\Response::class);
				$body = new \Amp\ByteStream\Payload(json_encode($data));
				$response->method('getBody')->willReturn($body);

				return $response;
			});

		\Aviat\AnimeClient\getApiClient($client);
		$this->requestBuilder
			->method('queryRequest')
			->willReturn(new \Amp\Http\Client\Request('https://example.com'));
		$this->container->get('config')->set('kitsu_username', 'test_user');

		$result = $this->model->getAnimeList('current');
		$this->assertCount(2, $result);
		$this->assertArrayHasKey('1', $result);
		$this->assertArrayHasKey('2', $result);
	}

	public function testGetSyncList(): void
	{
		$client = $this->createMock(\Amp\Http\Client\HttpClient::class);
		$jsonData = ['data' => ['findProfileBySlug' => ['library' => ['all' => [
			'nodes' => [],
			'pageInfo' => ['endCursor' => '', 'hasNextPage' => false],
		]]]]];
		$client
			->method('request')
			->willReturnCallback(function () use ($jsonData) {
				$response = $this->createMock(\Amp\Http\Client\Response::class);
				$body = new \Amp\ByteStream\Payload(json_encode($jsonData));
				$response->method('getBody')->willReturn($body);

				return $response;
			});

		\Aviat\AnimeClient\getApiClient($client);
		$this->requestBuilder
			->method('queryRequest')
			->willReturn(new \Amp\Http\Client\Request('https://example.com'));

		$this->container->get('config')->set('kitsu_username', 'test_user');

		$result = $this->model->getSyncList('anime');
		$this->assertEquals([], $result);
	}

	public function testGetFullOrganizedAnimeListCache(): void
	{
		$cache = $this->createMock(\Psr\SimpleCache\CacheInterface::class);
		$cache->method('get')
			->willReturn(['cached' => 'data']);
		$this->model->setCache($cache);

		$result = $this->model->getFullOrganizedAnimeList();
		$this->assertArrayHasKey('Currently Watching', $result);
	}

	public function testGetFullOrganizedMangaListCache(): void
	{
		$cache = $this->createMock(\Psr\SimpleCache\CacheInterface::class);
		$cache->method('get')
			->willReturn(['cached' => 'manga']);
		$this->model->setCache($cache);

		$result = $this->model->getFullOrganizedMangaList();
		$this->assertArrayHasKey('Currently Reading', $result);
	}
}
