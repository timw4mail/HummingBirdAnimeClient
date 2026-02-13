<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.4
 *
 * @copyright   2015 - 2026  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.3
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests\API;

use Aviat\AnimeClient\API\Kitsu\Enum\AnimeAiringStatus;
use Aviat\AnimeClient\API\Kitsu\Enum\MangaPublishingStatus;
use Aviat\AnimeClient\Kitsu;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class KitsuTest extends TestCase
{
	public function testGetAiringStatus(): void
	{
		$actual = Kitsu::getAiringStatus('next week', 'next year');
		$this->assertSame(AnimeAiringStatus::NOT_YET_AIRED, $actual);
	}

	public function testParseStreamingLinksEmpty(): void
	{
		$this->assertSame([], Kitsu::parseStreamingLinks([]));
	}

	public function testParseStreamingLinks(): void
	{
		$nodes = [[
			'url' => 'www.hulu.com/chobits',
			'dubs' => ['ja'],
			'subs' => ['en'],
		]];

		$expected = [[
			'meta' => [
				'name' => 'Hulu',
				'link' => true,
				'image' => 'streaming-logos/hulu.svg',
			],
			'link' => 'www.hulu.com/chobits',
			'dubs' => ['ja'],
			'subs' => ['en'],
		]];

		$this->assertEquals($expected, Kitsu::parseStreamingLinks($nodes));
	}

	public function testParseStreamingLinksNoHost(): void
	{
		$nodes = [[
			'url' => '/link-fragment',
			'dubs' => [],
			'subs' => [],
		]];

		$this->assertSame([], Kitsu::parseStreamingLinks($nodes));
	}

	public function testGetAiringStatusEmptyArguments(): void
	{
		$this->assertSame(AnimeAiringStatus::NOT_YET_AIRED, Kitsu::getAiringStatus());
	}

	public function testGetAiringStatusIsAiring(): void
	{
		$this->assertSame(AnimeAiringStatus::AIRING, Kitsu::getAiringStatus('yesterday'));
	}

	public function testGetPublishingStatusEmptyArguments(): void
	{
		$this->assertSame(MangaPublishingStatus::NOT_YET_PUBLISHED, Kitsu::getPublishingStatus());
	}

	public function testGetPublishingStatusIsPublishing(): void
	{
		$this->assertSame(MangaPublishingStatus::CURRENT, Kitsu::getPublishingStatus('yesterday'));
	}

	public function testFilterLocalizedTitles(): void
	{
		$input = [
			'canonical' => 'foo',
			'localized' => [
				'en' => 'Foo the Movie',
				'fr' => '',
				'jp' => null,
			],
			'alternatives' => [],
		];

		$actual = Kitsu::filterLocalizedTitles($input);

		$this->assertSame(['Foo the Movie'], $actual);
	}

	public function testGetFilteredTitles(): void
	{
		$input = [
			'canonical' => 'foo',
			'localized' => [
				'en' => 'Foo the Movie',
			],
			'alternatives' => [],
		];

		$actual = Kitsu::getFilteredTitles($input);

		$this->assertSame(['Foo the Movie'], $actual);
	}

	public function testFormatAirDates(): void
	{
		$this->assertEquals('January 01, 2020', Kitsu::formatAirDates('2020-01-01', '2020-01-01'));
		$this->assertEquals('January 2020 - ', Kitsu::formatAirDates('2020-01-01'));
		$this->assertEquals('January - February 2020', Kitsu::formatAirDates('2020-01-01', '2020-02-01'));
		$this->assertEquals('January 2020 - January 2021', Kitsu::formatAirDates(
			'2020-01-01',
			'2021-01-01',
		));
	}

	public function testMappingsToUrls(): void
	{
		$mappings = [
			['externalSite' => 'MYANIMELIST_ANIME', 'externalId' => '1'],
			['externalSite' => 'ANILIST_ANIME', 'externalId' => '2'],
		];
		$urls = Kitsu::mappingsToUrls($mappings, 'http://kitsu.example.com');
		$this->assertArrayHasKey('MyAnimeList', $urls);
		$this->assertArrayHasKey('Anilist', $urls);
		$this->assertArrayHasKey('Kitsu', $urls);
		$this->assertEquals('https://myanimelist.net/anime/1', $urls['MyAnimeList']);
	}

	public function testGetImage(): void
	{
		$data = [
			'image' => ['original' => ['url' => 'test.jpg']],
		];
		$this->assertEquals('test.jpg', Kitsu::getImage($data));
		$this->assertEquals('/public/images/placeholder.png', Kitsu::getImage([]));
	}

	public function testGetTitles(): void
	{
		$input = [
			'canonical' => 'foo',
			'localized' => [
				'en' => 'Foo',
				'ja_jp' => 'Fu',
			],
		];
		$actual = Kitsu::getTitles($input);
		$this->assertContains('Foo', $actual);
		$this->assertContains('Fu', $actual);
	}

	public function testGetPublishingStatus(): void
	{
		$this->assertEquals(MangaPublishingStatus::FINISHED, Kitsu::getPublishingStatus(
			'2020-01-01',
			'2020-12-31',
		));
		$this->assertEquals(MangaPublishingStatus::CURRENT, Kitsu::getPublishingStatus(
			'2020-01-01',
			'next year',
		));
	}
}
