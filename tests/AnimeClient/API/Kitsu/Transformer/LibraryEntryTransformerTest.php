<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\API\Kitsu\Transformer;

use Aviat\AnimeClient\API\Kitsu\Transformer\LibraryEntryTransformer;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use Aviat\AnimeClient\Types\AnimeListItem;
use Aviat\AnimeClient\Types\MangaListItem;

/**
 * @internal
 */
final class LibraryEntryTransformerTest extends AnimeClientTestCase
{
	public function testTransformAnime(): void
	{
		$transformer = new LibraryEntryTransformer();
		$data = [
			'id' => '1',
			'status' => 'CURRENT',
			'progress' => 5,
			'rating' => 16,
			'reconsuming' => false,
			'reconsumeCount' => 0,
			'notes' => 'Notes',
			'media' => [
				'id' => '10',
				'type' => 'Anime',
				'slug' => 'test-anime',
				'subtype' => 'TV',
				'episodeCount' => 12,
				'episodeLength' => 24,
				'startDate' => '2020-01-01',
				'endDate' => '2020-12-31',
				'ageRating' => 'G',
				'titles' => ['canonical' => 'Test', 'localized' => []],
				'categories' => ['nodes' => []],
				'mappings' => [
					'nodes' => [
						['externalSite' => 'MYANIMELIST_ANIME', 'externalId' => '123'],
						['externalSite' => 'ANILIST_ANIME', 'externalId' => '456'],
					],
				],
				'streamingLinks' => ['nodes' => []],
				'posterImage' => ['original' => ['url' => 'test.jpg']],
			],
		];

		$result = $transformer->transform($data);
		$this->assertInstanceOf(AnimeListItem::class, $result);
		$this->assertEquals('123', $result['mal_id']);
		$this->assertEquals('456', $result['anilist_id']);
		$this->assertEquals(8, $result['user_rating']);
	}

	public function testTransformAnimeMinimal(): void
	{
		$transformer = new LibraryEntryTransformer();
		$data = [
			'id' => '1',
			'status' => 'CURRENT',
			'progress' => 0,
			'rating' => 0,
			'reconsuming' => false,
			'reconsumeCount' => 0,
			'notes' => '',
			'media' => [
				'id' => '10',
				'type' => 'Anime',
				'slug' => 'test-anime',
				'subtype' => 'TV',
				'episodeCount' => 0,
				'episodeLength' => 24,
				'startDate' => null,
				'endDate' => null,
				'ageRating' => 'G',
				'titles' => ['canonical' => 'Test', 'localized' => []],
				'categories' => ['nodes' => []],
				'mappings' => ['nodes' => []],
				'streamingLinks' => [], // No nodes key
				'posterImage' => [],
			],
		];

		$result = $transformer->transform($data);
		$this->assertInstanceOf(AnimeListItem::class, $result);
		$this->assertEquals('-', $result['episodes']['watched']);
		$this->assertEquals('-', $result['episodes']['total']);
		$this->assertEquals('-', $result['user_rating']);
	}

	public function testTransformManga(): void
	{
		$transformer = new LibraryEntryTransformer();
		$data = [
			'id' => '2',
			'status' => 'CURRENT',
			'progress' => 10,
			'rating' => 14,
			'reconsuming' => true,
			'reconsumeCount' => 1,
			'notes' => 'Manga Notes',
			'media' => [
				'id' => '20',
				'type' => 'Manga',
				'slug' => 'test-manga',
				'subtype' => 'MANGA',
				'chapterCount' => 50,
				'volumeCount' => 5,
				'titles' => ['canonical' => 'Test Manga', 'localized' => []],
				'categories' => ['nodes' => []],
				'mappings' => [
					'nodes' => [
						['externalSite' => 'MYANIMELIST_MANGA', 'externalId' => '789'],
						['externalSite' => 'ANILIST_MANGA', 'externalId' => '012'],
					],
				],
				'posterImage' => ['original' => ['url' => 'manga.jpg']],
			],
		];

		$result = $transformer->transform($data);
		$this->assertInstanceOf(MangaListItem::class, $result);
		$this->assertEquals('789', $result['mal_id']);
		$this->assertEquals('012', $result['anilist_id']);
		$this->assertEquals(7, $result['user_rating']);
	}

	public function testTransformMangaMinimal(): void
	{
		$transformer = new LibraryEntryTransformer();
		$data = [
			'id' => '2',
			'status' => 'CURRENT',
			'progress' => 0,
			'rating' => 0,
			'reconsuming' => false,
			'reconsumeCount' => 0,
			'notes' => '',
			'media' => [
				'id' => '20',
				'type' => 'Manga',
				'slug' => 'test-manga',
				'subtype' => 'MANGA',
				'chapterCount' => 0,
				'volumeCount' => 0,
				'titles' => ['canonical' => 'Test Manga', 'localized' => []],
				'categories' => ['nodes' => []],
				'mappings' => ['nodes' => []],
				'posterImage' => [],
			],
		];

		$result = $transformer->transform($data);
		$this->assertInstanceOf(MangaListItem::class, $result);
		$this->assertEquals('-', $result['chapters']['read']);
		$this->assertEquals('-', $result['chapters']['total']);
		$this->assertEquals('-', $result['user_rating']);
	}

	public function testTransformDefault(): void
	{
		$transformer = new LibraryEntryTransformer();
		$data = [
			'media' => [
				'type' => 'Unknown',
				'categories' => ['nodes' => []],
			],
		];
		$result = $transformer->transform($data);
		$this->assertInstanceOf(AnimeListItem::class, $result);
	}
}
