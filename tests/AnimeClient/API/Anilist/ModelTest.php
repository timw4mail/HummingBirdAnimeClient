<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\API\Anilist;

use Aviat\AnimeClient\API\Anilist\ListItem;
use Aviat\AnimeClient\API\Anilist\Model;
use Aviat\AnimeClient\API\Anilist\RequestBuilder;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * @internal
 */
#[AllowMockObjectsWithoutExpectations]
final class ModelTest extends AnimeClientTestCase
{
	protected Model $model;

	protected $requestBuilder;

	protected $listItem;

	#[\Override]
	protected function setUp(): void
	{
		parent::setUp();

		$this->requestBuilder = $this->createMock(RequestBuilder::class);
		$this->listItem = $this->createStub(ListItem::class);

		$this->model = new Model($this->listItem);
		$this->model->setRequestBuilder($this->requestBuilder);
		$this->model->setContainer($this->container);
	}

	public function testCheckAuth(): void
	{
		$this->requestBuilder
			->expects($this->once())
			->method('runQuery')
			->with('CheckLogin')
			->willReturn(['data' => ['Viewer' => ['name' => 'test']]]);

		$result = $this->model->checkAuth();
		$this->assertEquals('test', $result['data']['Viewer']['name']);
	}

	public function testGetSyncList(): void
	{
		$this->container->get('config')->set(['anilist', 'username'], 'test_user');

		$this->requestBuilder
			->expects($this->once())
			->method('runQuery')
			->with('SyncUserList', ['name' => 'test_user', 'type' => 'anime'])
			->willReturn(['data' => []]);

		$result = $this->model->getSyncList('anime');
		$this->assertEquals(['data' => []], $result);
	}

	public function testCreateListItem(): void
	{
		$data = [
			'anilist_id' => '123',
			'status' => 'current',
		];

		$this->listItem = $this->createMock(ListItem::class);
		$this->model = new Model($this->listItem);
		$this->model->setRequestBuilder($this->requestBuilder);
		$this->model->setContainer($this->container);

		$this->listItem
			->expects($this->once())
			->method('create')
			->with([
				'id' => '123',
				'status' => 'CURRENT',
			])
			->willReturn(new \Amp\Http\Client\Request('https://example.com', 'POST'));

		$result = $this->model->createListItem($data, 'ANIME');
		$this->assertInstanceOf(\Amp\Http\Client\Request::class, $result);
	}

	public function testUpdateListItem(): void
	{
		$formItem = \Aviat\AnimeClient\Types\FormItem::from([
			'anilist_id' => '123',
			'data' => \Aviat\AnimeClient\Types\FormItemData::from(['progress' => 5]),
		]);

		$this->listItem = $this->createMock(ListItem::class);
		$this->model = new Model($this->listItem);
		$this->model->setRequestBuilder($this->requestBuilder);
		$this->model->setContainer($this->container);

		$this->container->get('config')->set(['anilist', 'username'], 'test_user');

		$this->requestBuilder
			->expects($this->once())
			->method('runQuery')
			->with('ListItemIdByMediaId', [
				'id' => '123',
				'userName' => 'test_user',
			])
			->willReturn(['data' => ['MediaList' => ['id' => '456']]]);

		$this->listItem
			->expects($this->once())
			->method('update')
			->with('456', $formItem['data'])
			->willReturn(new \Amp\Http\Client\Request('https://example.com', 'POST'));

		$result = $this->model->updateListItem($formItem, 'ANIME');
		$this->assertInstanceOf(\Amp\Http\Client\Request::class, $result);
	}

	public function testAuthenticate(): void
	{
		$response = $this->createMock(\Amp\Http\Client\Response::class);
		$body = new \Amp\ByteStream\Payload(json_encode(['access_token' => 'foo']));
		$response->method('getBody')->willReturn($body);

		$this->requestBuilder
			->expects($this->once())
			->method('newRequest')
			->willReturnSelf();
		$this->requestBuilder
			->expects($this->once())
			->method('setJsonBody')
			->willReturnSelf();
		$this->requestBuilder
			->expects($this->once())
			->method('getFullRequest')
			->willReturn(new \Amp\Http\Client\Request('https://example.com', 'POST'));
		$this->requestBuilder
			->expects($this->once())
			->method('getResponseFromRequest')
			->willReturn($response);

		$result = $this->model->authenticate('code', 'uri');
		$this->assertEquals(['access_token' => 'foo'], $result);
	}

	public function testIncrementListItem(): void
	{
		$formItem = \Aviat\AnimeClient\Types\FormItem::from([
			'anilist_id' => '123',
			'data' => \Aviat\AnimeClient\Types\FormItemData::from(['progress' => 5]),
		]);

		$this->listItem = $this->createMock(ListItem::class);
		$this->model = new Model($this->listItem);
		$this->model->setRequestBuilder($this->requestBuilder);
		$this->model->setContainer($this->container);

		$this->container->get('config')->set(['anilist', 'username'], 'test_user');

		$this->requestBuilder
			->expects($this->once())
			->method('runQuery')
			->with('ListItemIdByMediaId', [
				'id' => '123',
				'userName' => 'test_user',
			])
			->willReturn(['data' => ['MediaList' => ['id' => '456']]]);

		$this->listItem
			->expects($this->once())
			->method('increment')
			->with('456', $formItem['data'])
			->willReturn(new \Amp\Http\Client\Request('https://example.com', 'POST'));

		$result = $this->model->incrementListItem($formItem, 'ANIME');
		$this->assertInstanceOf(\Amp\Http\Client\Request::class, $result);
	}

	public function testDeleteItem(): void
	{
		$formItem = \Aviat\AnimeClient\Types\FormItem::from([
			'anilist_id' => '123',
		]);

		$this->listItem = $this->createMock(ListItem::class);
		$this->model = new Model($this->listItem);
		$this->model->setRequestBuilder($this->requestBuilder);
		$this->model->setContainer($this->container);

		$this->container->get('config')->set(['anilist', 'username'], 'test_user');

		$this->requestBuilder
			->expects($this->once())
			->method('runQuery')
			->with('ListItemIdByMediaId', [
				'id' => '123',
				'userName' => 'test_user',
			])
			->willReturn(['data' => ['MediaList' => ['id' => '456']]]);

		$this->listItem
			->expects($this->once())
			->method('delete')
			->with('456')
			->willReturn(new \Amp\Http\Client\Request('https://example.com', 'POST'));

		$result = $this->model->deleteItem($formItem, 'ANIME');
		$this->assertInstanceOf(\Amp\Http\Client\Request::class, $result);
	}

	public function testCreateFullListItem(): void
	{
		$data = [
			'id' => '123',
			'anilist_id' => '456',
		];
		$this->listItem = $this->createMock(ListItem::class);
		$this->model = new Model($this->listItem);
		$this->model->setRequestBuilder($this->requestBuilder);
		$this->model->setContainer($this->container);

		$this->listItem
			->expects($this->once())
			->method('createFull')
			->willReturn(new \Amp\Http\Client\Request('https://example.com', 'POST'));

		$result = $this->model->createFullListItem($data, 'ANIME');
		$this->assertInstanceOf(\Amp\Http\Client\Request::class, $result);
	}
}
