<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\API\Kitsu\Transformer;

use Aviat\AnimeClient\API\Kitsu\Transformer\MangaListTransformer;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use Aviat\AnimeClient\Types\MangaListItem;

/**
 * @internal
 */
final class MangaListTransformerTest extends AnimeClientTestCase
{
	public function testTransform(): void
	{
		$transformer = new MangaListTransformer();
		$data = [
			'id' => '1',
			'status' => 'CURRENT',
			'progress' => 10,
			'rating' => 16,
			'reconsuming' => false,
			'reconsumeCount' => 0,
			'notes' => 'Notes',
			'media' => [
				'id' => '10',
				'slug' => 'test-manga',
				'subtype' => 'MANGA',
				'chapterCount' => 50,
				'volumeCount' => 5,
				'titles' => ['canonical' => 'Test', 'localized' => []],
				'mappings' => [
					'nodes' => [
						['externalSite' => 'UNKNOWN', 'externalId' => '0'],
						['externalSite' => 'MYANIMELIST_MANGA', 'externalId' => '123'],
					],
				],
				'posterImage' => ['original' => ['url' => 'test.jpg']],
			],
		];

		$result = $transformer->transform($data);
		$this->assertInstanceOf(MangaListItem::class, $result);
		$this->assertEquals('123', $result['mal_id']);
		$this->assertEquals(8, $result['user_rating']);
	}

	public function testUntransform(): void
	{
		$transformer = new MangaListTransformer();
		$item = [
			'id' => '1',
			'mal_id' => '123',
			'status' => 'completed',
			'rereading' => false,
			'reread_count' => 0,
			'notes' => 'Notes',
			'chapters_read' => 50,
			'new_rating' => 8,
		];

		$result = $transformer->untransform($item);
		$this->assertEquals('1', $result['id']);
		$this->assertEquals(16, $result['data']['ratingTwenty']);
	}
}
