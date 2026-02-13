<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\Model;

use Aviat\AnimeClient\Model\Manga as MangaModel;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use Aviat\AnimeClient\Types\MangaPage;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * @internal
 */
#[AllowMockObjectsWithoutExpectations]
final class MangaTest extends AnimeClientTestCase
{
	protected MangaModel $model;

	protected $kitsuModel;

	#[\Override]
	protected function setUp(): void
	{
		parent::setUp();

		$this->kitsuModel = $this->createMock(\Aviat\AnimeClient\API\Kitsu\Model::class);
		$this->container->setInstance('kitsu-model', $this->kitsuModel);

		$this->model = new MangaModel($this->container);
	}

	public function testGetListAll(): void
	{
		$this->kitsuModel
			->expects($this->once())
			->method('getFullOrganizedMangaList')
			->willReturn([
				'Currently Reading' => [
					'1' => [
						'id' => '1',
						'manga' => ['title' => 'B Manga'],
					],
					'2' => [
						'id' => '2',
						'manga' => ['title' => 'A Manga'],
					],
				],
			]);

		$result = $this->model->getList('All');
		$this->assertArrayHasKey('Currently Reading', $result);
		$keys = array_keys($result['Currently Reading']);
		$this->assertEquals(['2', '1'], $keys);
	}

	public function testGetListByStatus(): void
	{
		$this->kitsuModel
			->expects($this->once())
			->method('getMangaList')
			->with('current')
			->willReturn([
				'1' => [
					'id' => '1',
					'reading_status' => 'current',
					'manga' => ['title' => 'A Manga'],
				],
			]);

		$result = $this->model->getList('Currently Reading');
		$this->assertArrayHasKey('1', $result);
		$this->assertEquals('A Manga', $result['1']['manga']['title']);
	}

	public function testGetManga(): void
	{
		$manga = MangaPage::from(['title' => 'Test Manga']);
		$this->kitsuModel
			->expects($this->once())
			->method('getManga')
			->with('test-slug')
			->willReturn($manga);

		$this->assertSame($manga, $this->model->getManga('test-slug'));
	}

	public function testGetHistory(): void
	{
		$history = [['id' => '1']];
		$this->kitsuModel
			->expects($this->once())
			->method('getMangaHistory')
			->willReturn($history);

		$this->assertSame($history, $this->model->getHistory());
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

		$client = $this->createMock(\Amp\Http\Client\HttpClient::class);
		$response = $this->createMock(\Amp\Http\Client\Response::class);
		$body = new \Amp\ByteStream\Payload('{"data": "ok"}');
		$response->method('getBody')->willReturn($body);
		$client->method('request')->willReturn($response);
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

		$client = $this->createMock(\Amp\Http\Client\HttpClient::class);
		$response = $this->createMock(\Amp\Http\Client\Response::class);
		$body = new \Amp\ByteStream\Payload('{"data": "ok"}');
		$response->method('getBody')->willReturn($body);
		$client->method('request')->willReturn($response);
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

		$client = $this->createMock(\Amp\Http\Client\HttpClient::class);
		$response = $this->createMock(\Amp\Http\Client\Response::class);
		$body = new \Amp\ByteStream\Payload('{"data": "ok"}');
		$response->method('getBody')->willReturn($body);
		$client->method('request')->willReturn($response);
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

		$client = $this->createMock(\Amp\Http\Client\HttpClient::class);
		$response = $this->createMock(\Amp\Http\Client\Response::class);
		$body = new \Amp\ByteStream\Payload('{"data": "ok"}');
		$response->method('getBody')->willReturn($body);
		$client->method('request')->willReturn($response);
		\Aviat\AnimeClient\getApiClient($client);

		$this->assertTrue($this->model->deleteItem($formItem));
	}
}
