<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\API\Kitsu;

use Aviat\AnimeClient\API\Kitsu\ListItem;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * @internal
 */
#[AllowMockObjectsWithoutExpectations]
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
			'id' => '1',
			'status' => 'current',
			'type' => 'anime',
			'user_id' => '10',
		];
		$this->requestBuilder->expects($this->once())
			->method('mutateRequest')
			->with('CreateLibraryItem', [
				'id' => '1',
				'status' => 'CURRENT',
				'type' => 'ANIME',
				'userId' => '10',
			])
			->willReturn(new \Amp\Http\Client\Request('https://example.com'));

		$this->listItem->create($data);
	}

	public function testCreateFull(): void
	{
		$data = [
			'id' => '1',
			'status' => 'current',
			'type' => 'anime',
			'user_id' => '10',
			'notes' => 'Notes',
		];
		
		$auth = $this->createMock(\Aviat\AnimeClient\API\Kitsu\Auth::class);
		$auth->method('getAuthToken')->willReturn('token');
		$this->container->setInstance('auth', $auth);

		$this->requestBuilder->expects($this->once())->method('newRequest')->willReturnSelf();
		$this->requestBuilder->expects($this->once())->method('setHeader')->with('Authorization', 'bearer token')->willReturnSelf();
		$this->requestBuilder->expects($this->once())->method('setJsonBody')->willReturnSelf();
		$this->requestBuilder->expects($this->once())->method('getFullRequest')->willReturn(new \Amp\Http\Client\Request('https://example.com'));

		$result = $this->listItem->createFull($data);
		$this->assertInstanceOf(\Amp\Http\Client\Request::class, $result);
	}

	public function testCreateFullNoAuth(): void
	{
		$data = [
			'id' => '1',
			'status' => 'current',
			'type' => 'anime',
			'user_id' => '10',
		];
		
		$auth = $this->createMock(\Aviat\AnimeClient\API\Kitsu\Auth::class);
		$auth->method('getAuthToken')->willReturn(null);
		$this->container->setInstance('auth', $auth);

		$this->requestBuilder->expects($this->once())->method('newRequest')->willReturnSelf();
		$this->requestBuilder->expects($this->never())->method('setHeader');
		$this->requestBuilder->expects($this->once())->method('setJsonBody')->willReturnSelf();
		$this->requestBuilder->expects($this->once())->method('getFullRequest')->willReturn(new \Amp\Http\Client\Request('https://example.com'));

		$result = $this->listItem->createFull($data);
		$this->assertInstanceOf(\Amp\Http\Client\Request::class, $result);
	}

	public function testDelete(): void
	{
		$this->requestBuilder->expects($this->once())
			->method('mutateRequest')
			->with('DeleteLibraryItem', ['id' => '1'])
			->willReturn(new \Amp\Http\Client\Request('https://example.com'));

		$this->listItem->delete('1');
	}

	public function testGet(): void
	{
		$this->requestBuilder->expects($this->once())
			->method('runQuery')
			->with('GetLibraryItem', ['id' => '1'])
			->willReturn([]);

		$this->listItem->get('1');
	}

	public function testIncrement(): void
	{
		$data = \Aviat\AnimeClient\Types\FormItemData::from(['progress' => 5]);
		$this->requestBuilder->expects($this->once())
			->method('mutateRequest')
			->with('IncrementLibraryItem', [
				'id' => '1',
				'progress' => 5,
			])
			->willReturn(new \Amp\Http\Client\Request('https://example.com'));

		$this->listItem->increment('1', $data);
	}

	public function testUpdate(): void
	{
		$data = \Aviat\AnimeClient\Types\FormItemData::from([
			'notes' => 'Notes',
			'private' => true,
			'reconsumeCount' => 1,
			'reconsuming' => false,
			'status' => 'completed',
			'progress' => 12,
			'ratingTwenty' => 16,
		]);
		$this->requestBuilder->expects($this->once())
			->method('mutateRequest')
			->with('UpdateLibraryItem', [
				'id' => '1',
				'notes' => 'Notes',
				'private' => true,
				'reconsumeCount' => 1,
				'reconsuming' => false,
				'status' => 'COMPLETED',
				'progress' => 12,
				'ratingTwenty' => 16,
			])
			->willReturn(new \Amp\Http\Client\Request('https://example.com'));

		$this->listItem->update('1', $data);
	}
}
