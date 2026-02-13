<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\API\Anilist;

use Aviat\AnimeClient\API\Anilist\Model;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;

final class ModelTest extends AnimeClientTestCase
{
	protected Model $model;

	protected $requestBuilder;

	protected $listItem;

	#[\Override]
	protected function setUp(): void
	{
		parent::setUp();

		$this->requestBuilder = $this->createMock(\Aviat\AnimeClient\API\Anilist\RequestBuilder::class);
		$this->listItem = $this->createStub(\Aviat\AnimeClient\API\Anilist\ListItem::class);

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
}
