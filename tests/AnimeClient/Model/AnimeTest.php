<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\Model;

use Aviat\AnimeClient\Model\Anime as AnimeModel;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use Aviat\AnimeClient\Types\Anime as AnimeType;

final class AnimeTest extends AnimeClientTestCase
{
	protected AnimeModel $model;

	protected $kitsuModel;

	protected $anilistModel;

	#[\Override]
	protected function setUp(): void
	{
		parent::setUp();

		$this->kitsuModel = $this->createMock(\Aviat\AnimeClient\API\Kitsu\Model::class);
		$this->container->setInstance('kitsu-model', $this->kitsuModel);

		$this->anilistModel = $this->createStub(\Aviat\AnimeClient\API\Anilist\Model::class);
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
}
