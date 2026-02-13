<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\API\Kitsu;

use Aviat\AnimeClient\API\Kitsu\ListItem;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use Aviat\AnimeClient\Types\FormItemData;

final class ListItemTest extends AnimeClientTestCase
{
	protected ListItem $listItem;

	protected $requestBuilder;

	#[\Override]
	protected function setUp(): void
	{
		parent::setUp();

		$this->requestBuilder = $this->createMock(\Aviat\AnimeClient\API\Kitsu\RequestBuilder::class);
		$this->listItem = new ListItem();
		$this->listItem->setRequestBuilder($this->requestBuilder);
		$this->listItem->setContainer($this->container);
	}

	public function testCreate(): void
	{
		$data = [
			'id' => '123',
			'status' => 'current',
			'type' => 'anime',
			'user_id' => '456',
		];

		$this->requestBuilder
			->expects($this->once())
			->method('mutateRequest')
			->with('CreateLibraryItem', [
				'id' => '123',
				'status' => 'CURRENT',
				'type' => 'ANIME',
				'userId' => '456',
			]);

		$this->listItem->create($data);
	}

	public function testDelete(): void
	{
		$this->requestBuilder
			->expects($this->once())
			->method('mutateRequest')
			->with('DeleteLibraryItem', ['id' => '123']);

		$this->listItem->delete('123');
	}

	public function testGet(): void
	{
		$this->requestBuilder
			->expects($this->once())
			->method('runQuery')
			->with('GetLibraryItem', ['id' => '123'])
			->willReturn(['data' => []]);

		$result = $this->listItem->get('123');
		$this->assertEquals(['data' => []], $result);
	}

	public function testIncrement(): void
	{
		$data = FormItemData::from(['progress' => 5]);
		$this->requestBuilder
			->expects($this->once())
			->method('mutateRequest')
			->with('IncrementLibraryItem', ['id' => '123', 'progress' => 5]);

		$this->listItem->increment('123', $data);
	}

	public function testUpdate(): void
	{
		$data = FormItemData::from([
			'notes' => 'Test notes',
			'private' => true,
			'reconsumeCount' => 1,
			'reconsuming' => false,
			'status' => 'current',
			'progress' => 10,
			'ratingTwenty' => 16,
		]);

		$this->requestBuilder
			->expects($this->once())
			->method('mutateRequest')
			->with('UpdateLibraryItem', [
				'id' => '123',
				'notes' => 'Test notes',
				'private' => true,
				'reconsumeCount' => 1,
				'reconsuming' => false,
				'status' => 'CURRENT',
				'progress' => 10,
				'ratingTwenty' => 16,
			]);

		$this->listItem->update('123', $data);
	}
}
