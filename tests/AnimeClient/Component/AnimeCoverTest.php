<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\Component;

use Aviat\AnimeClient\Component\AnimeCover;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use Aviat\AnimeClient\Types\AnimeListItem;

final class AnimeCoverTest extends AnimeClientTestCase
{
	public function testInvoke(): void
	{
		$component = new AnimeCover();
		$component->setContainer($this->container);

		$item = AnimeListItem::from([
			'id' => '123',
			'anilist_id' => '456',
			'mal_id' => '789',
			'anime' => [
				'id' => '1',
				'title' => 'Test Anime',
				'titles' => [],
				'slug' => 'test-anime',
				'cover_image' => 'test.jpg',
				'show_type' => 'TV',
				'age_rating' => 'PG13',
				'streaming_links' => [],
			],
			'watching_status' => 'current',
			'user_rating' => 10,
			'episodes' => [
				'watched' => 5,
				'total' => 12,
			],
			'airing' => [
				'status' => 'Currently Airing',
			],
			'rewatched' => 0,
		]);

		$output = $component($item);
		$this->assertStringContainsString('Test Anime', $output);
		$this->assertStringContainsString('data-kitsu-id="123"', $output);
		$this->assertStringContainsString('data-anilist-id="456"', $output);
		$this->assertStringContainsString('data-mal-id="789"', $output);
	}
}
