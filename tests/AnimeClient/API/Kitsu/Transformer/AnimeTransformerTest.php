<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\API\Kitsu\Transformer;

use Aviat\AnimeClient\API\Kitsu\Transformer\AnimeTransformer;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * @internal
 */
#[AllowMockObjectsWithoutExpectations]
final class AnimeTransformerTest extends AnimeClientTestCase
{
	public function testTransform(): void
	{
		$transformer = new AnimeTransformer();
		$data = [
			'data' => [
				'findAnimeBySlug' => [
					'id' => '1',
					'slug' => 'test-anime',
					'subtype' => 'TV',
					'episodeCount' => 12,
					'episodeLength' => 24,
					'totalLength' => 0,
					'titles' => ['canonical' => 'Test Anime', 'localized' => []],
					'posterImage' => ['original' => ['url' => 'test.jpg']],
					'ageRating' => 'G',
					'ageRatingGuide' => '',
					'description' => ['en' => 'Test'],
					'startDate' => '2020-01-01',
					'endDate' => '2020-12-31',
					'youtubeTrailerVideoId' => '123',
					'categories' => [
						'nodes' => [
							['title' => ['en' => 'Action']],
						],
					],
					'characters' => [
						'nodes' => [
							[
								'role' => 'MAIN',
								'character' => [
									'id' => '100',
									'slug' => 'char-slug',
									'names' => ['canonical' => 'Char Name'],
									'image' => ['original' => ['url' => 'char.jpg']],
								],
							],
						],
					],
					'staff' => [
						'nodes' => [
							[
								'role' => 'Director',
								'person' => [
									'id' => '200',
									'slug' => 'person-slug',
									'names' => [
										'canonical' => 'Person Name',
										'localized' => ['Person Name' => 'Actual Name'],
									],
									'image' => ['original' => ['url' => 'person.jpg']],
								],
							],
						],
					],
					'mappings' => [
						'nodes' => [
							['externalSite' => 'MYANIMELIST_ANIME', 'externalId' => '123'],
						],
					],
					'streamingLinks' => ['nodes' => []],
				],
			],
		];

		$result = $transformer->transform($data);
		$this->assertInstanceOf(\Aviat\AnimeClient\Types\AnimePage::class, $result);
		$this->assertArrayHasKey('main', $result['characters']);
		$this->assertArrayHasKey('Director', $result['staff']);
	}
}
