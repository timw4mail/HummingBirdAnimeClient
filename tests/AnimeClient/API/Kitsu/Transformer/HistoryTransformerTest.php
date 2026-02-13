<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\API\Kitsu\Transformer;

use Aviat\AnimeClient\API\Kitsu\Transformer\AnimeHistoryTransformer;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;

final class HistoryTransformerTest extends AnimeClientTestCase
{
	protected AnimeHistoryTransformer $transformer;

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
								'updatedAt' => '2026-02-12T12:00:00Z',
								'changedData' => [
									'progress' => [0, 1],
								],
								'libraryEntry' => [
									'private' => false,
									'reconsuming' => false,
								],
								'media' => [
									'__typename' => 'Anime',
									'slug' => 'test-anime',
									'titles' => ['canonical' => 'Test Anime'],
									'posterImage' => ['original' => ['url' => 'test.jpg']],
									'episodeCount' => 12,
								],
							],
							[
								'kind' => 'updated',
								'updatedAt' => '2026-02-12T13:00:00Z',
								'changedData' => [
									'status' => ['planned', 'current'],
								],
								'libraryEntry' => [
									'private' => false,
									'reconsuming' => false,
								],
								'media' => [
									'__typename' => 'Anime',
									'slug' => 'test-anime',
									'titles' => ['canonical' => 'Test Anime'],
									'posterImage' => ['original' => ['url' => 'test.jpg']],
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
								'updatedAt' => '2026-02-12T12:00:00Z',
								'changedData' => [
									'progress' => [0, 1],
								],
								'libraryEntry' => [
									'private' => false,
									'reconsuming' => false,
								],
								'media' => [
									'__typename' => 'Anime',
									'slug' => 'test-anime',
									'titles' => ['canonical' => 'Test Anime'],
									'posterImage' => ['original' => ['url' => 'test.jpg']],
									'episodeCount' => 12,
								],
							],
							[
								'kind' => 'progressed',
								'updatedAt' => '2026-02-12T12:30:00Z',
								'changedData' => [
									'progress' => [1, 2],
								],
								'libraryEntry' => [
									'private' => false,
									'reconsuming' => false,
								],
								'media' => [
									'__typename' => 'Anime',
									'slug' => 'test-anime',
									'titles' => ['canonical' => 'Test Anime'],
									'posterImage' => ['original' => ['url' => 'test.jpg']],
									'episodeCount' => 12,
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
}
