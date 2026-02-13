<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\API\Kitsu\Transformer;

use Aviat\AnimeClient\API\Kitsu\Transformer\MangaHistoryTransformer;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;

/**
 * @internal
 */
final class MangaHistoryTransformerTest extends AnimeClientTestCase
{
	protected $transformer;

	#[\Override]
	protected function setUp(): void
	{
		parent::setUp();
		$this->transformer = new MangaHistoryTransformer();
	}

	public function testTransform(): void
	{
		$data = [
			'data' => [
				'findProfileBySlug' => [
					'libraryEvents' => [
						'nodes' => [
							[
								'kind' => 'progressed',
								'updatedAt' => '2020-01-01T12:00:00Z',
								'libraryEntry' => ['private' => false, 'reconsuming' => false],
								'media' => [
									'__typename' => 'Manga',
									'id' => '1',
									'slug' => 'test',
									'titles' => ['canonical' => 'Test'],
									'posterImage' => ['original' => ['url' => 'test.jpg']],
								],
								'changedData' => [
									'progress' => [0, 1],
								],
							],
						],
					],
				],
			],
		];

		$result = $this->transformer->transform($data);
		$this->assertCount(1, $result);
		$this->assertEquals('Read chapter 1', $result[0]['action']);
	}

	public function testAggregate(): void
	{
		$data = [
			'data' => [
				'findProfileBySlug' => [
					'libraryEvents' => [
						'nodes' => [
							[
								'kind' => 'progressed',
								'updatedAt' => '2020-01-01T12:00:00Z',
								'libraryEntry' => ['private' => false, 'reconsuming' => false],
								'media' => [
									'__typename' => 'Manga',
									'id' => '1',
									'slug' => 'test',
									'titles' => ['canonical' => 'Test'],
									'posterImage' => ['original' => ['url' => 'test.jpg']],
								],
								'changedData' => [
									'progress' => [0, 1],
								],
							],
							[
								'kind' => 'progressed',
								'updatedAt' => '2020-01-01T12:05:00Z',
								'libraryEntry' => ['private' => false, 'reconsuming' => false],
								'media' => [
									'__typename' => 'Manga',
									'id' => '1',
									'slug' => 'test',
									'titles' => ['canonical' => 'Test'],
									'posterImage' => ['original' => ['url' => 'test.jpg']],
								],
								'changedData' => [
									'progress' => [1, 2],
								],
							],
						],
					],
				],
			],
		];

		$result = $this->transformer->transform($data);
		$this->assertCount(1, $result);
		$this->assertEquals('Read chapters 1-2', $result[0]['action']);
	}
}
