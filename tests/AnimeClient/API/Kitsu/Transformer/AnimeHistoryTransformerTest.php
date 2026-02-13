<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\API\Kitsu\Transformer;

use Aviat\AnimeClient\API\Kitsu\Transformer\AnimeHistoryTransformer;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use Aviat\AnimeClient\Types\HistoryItem;

/**
 * @internal
 */
final class AnimeHistoryTransformerTest extends AnimeClientTestCase
{
	protected $transformer;

	#[\Override]
	protected function setUp(): void
	{
		parent::setUp();
		$this->transformer = new AnimeHistoryTransformer();
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
									'__typename' => 'Anime',
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
								'kind' => 'updated',
								'updatedAt' => '2020-01-01T13:00:00Z',
								'libraryEntry' => ['private' => false, 'reconsuming' => false],
								'media' => [
									'__typename' => 'Anime',
									'id' => '1',
									'slug' => 'test',
									'titles' => ['canonical' => 'Test'],
									'posterImage' => ['original' => ['url' => 'test.jpg']],
								],
								'changedData' => [
									'status' => ['planned', 'current'],
								],
							],
						],
					],
				],
			],
		];

		$result = $this->transformer->transform($data);
		$this->assertCount(2, $result);
		$this->assertEquals('Watched episode 1', $result[0]['action']);
		$this->assertEquals('Currently Watching', $result[1]['action']);
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
									'__typename' => 'Anime',
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
									'__typename' => 'Anime',
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
		$this->assertTrue($result[0]['isAggregate']);
		$this->assertEquals('Watched episodes 1-2', $result[0]['action']);
	}

	public function testAggregateLarge(): void
	{
		$nodes = [];
		for ($i = 1; $i <= 5; $i++)
		{
			$nodes[] = [
				'kind' => 'progressed',
				'updatedAt' => '2020-01-01T12:0' . $i . ':00Z',
				'libraryEntry' => ['private' => false, 'reconsuming' => false],
				'media' => [
					'__typename' => 'Anime',
					'id' => '1',
					'slug' => 'test',
					'titles' => ['canonical' => 'Test'],
					'posterImage' => ['original' => ['url' => 'test.jpg']],
				],
				'changedData' => [
					'progress' => [$i-1, $i],
				],
			];
		}
		
		$data = [
			'data' => [
				'findProfileBySlug' => [
					'libraryEvents' => [
						'nodes' => $nodes,
					],
				],
			],
		];

		$result = $this->transformer->transform($data);
		$this->assertCount(1, $result);
		$this->assertEquals('Marathoned episodes 1-5', $result[0]['action']);
	}

	public function testReconsuming(): void
	{
		$data = [
			'data' => [
				'findProfileBySlug' => [
					'libraryEvents' => [
						'nodes' => [
							[
								'kind' => 'progressed',
								'updatedAt' => '2020-01-01T12:00:00Z',
								'libraryEntry' => ['private' => false, 'reconsuming' => true],
								'media' => [
									'__typename' => 'Anime',
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
		$this->assertEquals('Rewatched episode 1', $result[0]['action']);
	}

	public function testInvalidDate(): void
	{
		$data = [
			'data' => [
				'findProfileBySlug' => [
					'libraryEvents' => [
						'nodes' => [
							[
								'kind' => 'progressed',
								'updatedAt' => 'invalid-date',
								'libraryEntry' => ['private' => false, 'reconsuming' => false],
								'media' => [
									'__typename' => 'Anime',
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
		$this->assertInstanceOf(\DateTimeImmutable::class, $result[0]['updated']);
	}

	public function testTransformUpdatedOtherKind(): void
	{
		$data = [
			'data' => [
				'findProfileBySlug' => [
					'libraryEvents' => [
						'nodes' => [
							[
								'kind' => 'updated',
								'updatedAt' => '2020-01-01T13:00:00Z',
								'libraryEntry' => ['private' => false, 'reconsuming' => false],
								'media' => [
									'__typename' => 'Anime',
									'id' => '1',
									'slug' => 'test',
									'titles' => ['canonical' => 'Test'],
									'posterImage' => ['original' => ['url' => 'test.jpg']],
								],
								'changedData' => [
									'rating' => [14, 16],
								],
							],
						],
					],
				],
			],
		];

		$result = $this->transformer->transform($data);
		$this->assertCount(1, $result);
		$this->assertEquals('updated', $result[0]['kind']);
	}

	public function testTransformProgressLastEpisode(): void
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
									'__typename' => 'Anime',
									'id' => '1',
									'slug' => 'test',
									'titles' => ['canonical' => 'Test'],
									'posterImage' => ['original' => ['url' => 'test.jpg']],
									'episodeCount' => 12,
								],
								'changedData' => [
									'progress' => [11, 12],
								],
							],
						],
					],
				],
			],
		];

		$result = $this->transformer->transform($data);
		$this->assertCount(0, $result);
	}
}
