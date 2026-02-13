<?php declare(strict_types=1);

namespace Aviat\AnimeClient\Tests\Component;

use Aviat\AnimeClient\Component\MangaCover;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use Aviat\AnimeClient\Types\MangaListItem;
use Aviat\AnimeClient\Types\MangaListItemDetail;

final class MangaCoverTest extends AnimeClientTestCase
{
	public function testInvoke(): void
	{
		$component = new MangaCover();
		$component->setContainer($this->container);

		$item = MangaListItem::from([
			'id' => '123',
			'manga' => MangaListItemDetail::from([
				'id' => '1',
				'title' => 'Test Manga',
				'titles' => [],
				'slug' => 'test-manga',
				'image' => 'test.jpg',
				'type' => 'Manga',
				'url' => 'https://example.com',
			]),
			'reading_status' => 'current',
			'user_rating' => 10,
			'chapters' => [
				'read' => 5,
				'total' => 12,
			],
			'volumes' => [
				'read' => 1,
				'total' => 2,
			],
			'notes' => '',
			'rereading' => false,
			'reread' => 0,
		]);

		$output = $component($item, 'Test Name');
		$this->assertStringContainsString('Test Manga', $output);
	}
}
