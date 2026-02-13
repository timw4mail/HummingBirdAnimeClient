<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\API\Kitsu\Transformer;

use Aviat\AnimeClient\API\Kitsu\Transformer\MangaTransformer;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * @internal
 */
#[AllowMockObjectsWithoutExpectations]
final class MangaTransformerTest extends AnimeClientTestCase
{
	public function testTransform(): void
	{
		$transformer = new MangaTransformer();
		$data = [
			'data' => [
				'findMangaBySlug' => [
					'id' => '1',
					'slug' => 'test-manga',
					'subtype' => 'MANGA',
					'chapterCount' => 50,
					'volumeCount' => 5,
					'titles' => ['canonical' => 'Manga Test', 'localized' => []],
					'posterImage' => ['original' => ['url' => 'manga.jpg']],
					'ageRating' => 'G',
					'ageRatingGuide' => '',
					'description' => ['en' => 'Test'],
					'startDate' => '2020-01-01',
					'endDate' => '2020-12-31',
					'mappings' => ['nodes' => []],
					'categories' => ['nodes' => []],
					'characters' => ['nodes' => []],
					'staff' => ['nodes' => []],
				],
			],
		];

		$result = $transformer->transform($data);
		$this->assertInstanceOf(\Aviat\AnimeClient\Types\MangaPage::class, $result);
		$this->assertEquals('1', $result['id']);
		$this->assertEquals('https://kitsu.app/manga/test-manga', $result['url']);
	}

	public function testTransformFull(): void
	{
		$transformer = new MangaTransformer();
		$data = [
			'data' => [
				'findMangaBySlug' => [
					'id' => '1',
					'slug' => 'test-manga',
					'subtype' => 'MANGA',
					'chapterCount' => 50,
					'volumeCount' => 5,
					'titles' => ['canonical' => 'Manga Test', 'localized' => []],
					'posterImage' => ['original' => ['url' => 'manga.jpg']],
					'ageRating' => 'G',
					'ageRatingGuide' => '',
					'description' => ['en' => 'Test'],
					'startDate' => '2020-01-01',
					'endDate' => '2020-12-31',
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
								'role' => 'Story & Art',
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
							['externalSite' => 'MYANIMELIST_MANGA', 'externalId' => '123'],
						],
					],
				],
			],
		];

		$result = $transformer->transform($data);
		$this->assertArrayHasKey('main', $result['characters']);
		$this->assertArrayHasKey('Story & Art', $result['staff']);
		$this->assertArrayHasKey('MyAnimeList', $result['links']);
	}
}
