<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\API\Kitsu\Transformer;

use Aviat\AnimeClient\API\Kitsu\Transformer\LibraryEntryTransformer;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

/**
 * @internal
 */
#[AllowMockObjectsWithoutExpectations]
final class LibraryEntryTransformerTest extends AnimeClientTestCase
{
	public function testTransform(): void
	{
		$transformer = new LibraryEntryTransformer();
		$data = [
			'id' => '123',
			'status' => 'CURRENT',
			'progress' => 5,
			'rating' => 16,
			'reconsuming' => false,
			'reconsumeCount' => 0,
			'notes' => 'Test notes',
			'private' => false,
			'updatedAt' => '2020-01-01T00:00:00Z',
			'media' => [
				'id' => '10',
				'type' => 'anime',
				'slug' => 'test-anime',
				'subtype' => 'TV',
				'titles' => ['canonical' => 'Test', 'localized' => []],
				'posterImage' => ['original' => ['url' => 'test.jpg']],
				'episodeCount' => 12,
				'episodeLength' => 24,
				'totalLength' => 0,
				'ageRating' => 'G',
				'ageRatingGuide' => '',
				'streamingLinks' => ['nodes' => []],
				'mappings' => ['nodes' => []],
				'categories' => ['nodes' => []],
				'characters' => ['nodes' => []],
				'staff' => ['nodes' => []],
			],
		];

		$result = $transformer->transform($data);
		$this->assertEquals('123', $result['id']);
		$this->assertEquals('test-anime', $result['anime']['slug']);
	}

	public function testMangaTransform(): void
	{
		$transformer = new LibraryEntryTransformer();
		$data = [
			'id' => '456',
			'status' => 'CURRENT',
			'progress' => 10,
			'rating' => 18,
			'reconsuming' => true,
			'reconsumeCount' => 1,
			'notes' => 'Manga notes',
			'media' => [
				'id' => '20',
				'type' => 'manga',
				'slug' => 'test-manga',
				'subtype' => 'MANGA',
				'chapterCount' => 50,
				'volumeCount' => 5,
				'titles' => ['canonical' => 'Manga Test', 'localized' => []],
				'posterImage' => ['original' => ['url' => 'manga.jpg']],
				'mappings' => ['nodes' => []],
				'categories' => ['nodes' => []],
			],
		];

		$result = $transformer->transform($data);
		$this->assertInstanceOf(\Aviat\AnimeClient\Types\MangaListItem::class, $result);
		$this->assertEquals('456', $result['id']);
		$this->assertEquals('test-manga', $result['manga']['slug']);
	}

	public function testTransformEmpty(): void
	{
		$transformer = new LibraryEntryTransformer();
		$result = $transformer->transform([]);
		$this->assertInstanceOf(\Aviat\AnimeClient\Types\AnimeListItem::class, $result);
		$this->assertEmpty($result['id']);
	}
}
