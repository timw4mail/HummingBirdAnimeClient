<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\Model;

use Aviat\AnimeClient\Model\Manga as MangaModel;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use Aviat\AnimeClient\Types\MangaPage;

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
				[
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
}
