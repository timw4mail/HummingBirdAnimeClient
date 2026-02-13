<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\Model;

use Aviat\AnimeClient\Model\Anime as AnimeModel;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use Aviat\AnimeClient\Types\Anime as AnimeType;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * @internal
 */
#[AllowMockObjectsWithoutExpectations]
final class AnimeTest extends AnimeClientTestCase
{
	protected AnimeModel $model;

	protected $kitsuModel;

	protected $anilistModel;

	#[\Override]
	protected function setUp(): void
	{
		parent::setUp();

		$this->container->get('config')->set(['anilist', 'enabled'], true);

		$this->kitsuModel = $this->createMock(\Aviat\AnimeClient\API\Kitsu\Model::class);
		$this->container->setInstance('kitsu-model', $this->kitsuModel);

		$this->anilistModel = $this->createMock(\Aviat\AnimeClient\API\Anilist\Model::class);
		$this->container->setInstance('anilist-model', $this->anilistModel);

		$this->model = new AnimeModel($this->container);
	}

	public function testGetList(): void
	{
		$this->kitsuModel
			->expects($this->once())
			->method('getAnimeList')
			->with('current')
			->willReturn([
				'1' => [
					'id' => '1',
					'anime' => [
						'title' => 'B Anime',
					],
				],
				'2' => [
					'id' => '2',
					'anime' => [
						'title' => 'A Anime',
					],
				],
			]);

		$result = $this->model->getList('current');
		$this->assertArrayHasKey('Currently Watching', $result);

		// Check sorting
		$keys = array_keys($result['Currently Watching']);
		$this->assertEquals(['2', '1'], $keys);
	}

	public function testGetAnime(): void
	{
		$anime = AnimeType::from(['title' => 'Test Anime']);
		$this->kitsuModel
			->expects($this->once())
			->method('getAnime')
			->with('test-slug')
			->willReturn($anime);

		$this->assertSame($anime, $this->model->getAnime('test-slug'));
	}

	public function testGetAnimeById(): void
	{
		$anime = AnimeType::from(['title' => 'Test Anime']);
		$this->kitsuModel
			->expects($this->once())
			->method('getAnimeById')
			->with('123')
			->willReturn($anime);

		$this->assertSame($anime, $this->model->getAnimeById('123'));
	}

	public function testGetHistory(): void
	{
		$history = [['id' => '1']];
		$this->kitsuModel
			->expects($this->once())
			->method('getAnimeHistory')
			->willReturn($history);

		$this->assertSame($history, $this->model->getHistory());
	}

	public function testGetAllLists(): void
	{
		$this->kitsuModel
			->expects($this->once())
			->method('getFullOrganizedAnimeList')
			->willReturn(['Currently Watching' => []]);

		$result = $this->model->getAllLists();
		$this->assertArrayHasKey('Currently Watching', $result);
	}

	public function testSearch(): void
	{
		$this->kitsuModel
			->expects($this->once())
			->method('search')
			->with('anime', 'Test')
			->willReturn([['id' => '1']]);

		$result = $this->model->search('Test');
		$this->assertCount(1, $result);
	}

	public function testGetItem(): void
	{
		$this->kitsuModel
			->expects($this->once())
			->method('getListItem')
			->with('123')
			->willReturn(['id' => '123']);

		$result = $this->model->getItem('123');
		$this->assertEquals(['id' => '123'], $result);
	}

	public function testCreateItem(): void
	{
		$data = ['id' => '123'];
		$this->kitsuModel
			->expects($this->once())
			->method('createListItem')
			->willReturn(new \Amp\Http\Client\Request('https://example.com'));

		$this->anilistModel
			->expects($this->once())
			->method('createListItem')
			->willReturn(new \Amp\Http\Client\Request('https://example.com'));

		$client = $this->createMock(\Amp\Http\Client\HttpClient::class);
		$client
			->method('request')
			->willReturnCallback(function () {
				$response = $this->createMock(\Amp\Http\Client\Response::class);
				$body = new \Amp\ByteStream\Payload('{"data": "ok"}');
				$response->method('getBody')->willReturn($body);

				return $response;
			});
		\Aviat\AnimeClient\getApiClient($client);

		$this->assertTrue($this->model->createItem($data));
	}

	public function testIncrementItem(): void
	{
		$formItem = \Aviat\AnimeClient\Types\FormItem::from(['id' => '123']);
		$this->kitsuModel
			->expects($this->once())
			->method('incrementListItem')
			->willReturn(new \Amp\Http\Client\Request('https://example.com'));

		$this->anilistModel
			->expects($this->once())
			->method('incrementListItem')
			->willReturn(new \Amp\Http\Client\Request('https://example.com'));

		$client = $this->createMock(\Amp\Http\Client\HttpClient::class);
		$client
			->method('request')
			->willReturnCallback(function () {
				$response = $this->createMock(\Amp\Http\Client\Response::class);
				$body = new \Amp\ByteStream\Payload('{"data": "ok"}');
				$response->method('getBody')->willReturn($body);

				return $response;
			});
		\Aviat\AnimeClient\getApiClient($client);

		$result = $this->model->incrementItem($formItem);
		$this->assertEquals(200, $result['statusCode']);
	}

	public function testUpdateItem(): void
	{
		$formItem = \Aviat\AnimeClient\Types\FormItem::from(['id' => '123']);
		$this->kitsuModel
			->expects($this->once())
			->method('updateListItem')
			->willReturn(new \Amp\Http\Client\Request('https://example.com'));

		$this->anilistModel
			->expects($this->once())
			->method('updateListItem')
			->willReturn(new \Amp\Http\Client\Request('https://example.com'));

		$client = $this->createMock(\Amp\Http\Client\HttpClient::class);
		$client
			->method('request')
			->willReturnCallback(function () {
				$response = $this->createMock(\Amp\Http\Client\Response::class);
				$body = new \Amp\ByteStream\Payload('{"data": "ok"}');
				$response->method('getBody')->willReturn($body);

				return $response;
			});
		\Aviat\AnimeClient\getApiClient($client);

		$result = $this->model->updateItem($formItem);
		$this->assertEquals(200, $result['statusCode']);
	}

	public function testDeleteItem(): void
	{
		$formItem = \Aviat\AnimeClient\Types\FormItem::from(['id' => '123']);
		$this->kitsuModel
			->expects($this->once())
			->method('deleteItem')
			->willReturn(new \Amp\Http\Client\Request('https://example.com'));

		$this->anilistModel
			->expects($this->once())
			->method('deleteItem')
			->willReturn(new \Amp\Http\Client\Request('https://example.com'));

		$client = $this->createMock(\Amp\Http\Client\HttpClient::class);
		$client
			->method('request')
			->willReturnCallback(function () {
				$response = $this->createMock(\Amp\Http\Client\Response::class);
				$body = new \Amp\ByteStream\Payload('{"data": "ok"}');
				$response->method('getBody')->willReturn($body);

				return $response;
			});
		\Aviat\AnimeClient\getApiClient($client);

		$this->assertTrue($this->model->deleteItem($formItem));
	}
}
