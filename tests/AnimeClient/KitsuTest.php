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
	public function testGetFilteredTitles(): void
	{
		$input = [
			'canonical' => 'Foo the Movie',
			'localized' => [
				'en' => 'Foo the Movie',
				'ja_jp' => 'Fu',
			],
		];
		$actual = Kitsu::getFilteredTitles($input);

		$this->assertSame(['Fu'], $actual);

		$input = [
			'canonical' => 'Foo the Movie',
			'localized' => [
				'en' => 'Foo the Movie',
			],
		];
		$actual = Kitsu::getFilteredTitles($input);

		$this->assertSame([], $actual);
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
			['externalSite' => 'UNKNOWN_SITE', 'externalId' => '3'],
		];
		$urls = Kitsu::mappingsToUrls($mappings, 'http://kitsu.example.com');
		$this->assertArrayHasKey('MyAnimeList', $urls);
		$this->assertArrayHasKey('Anilist', $urls);
		$this->assertArrayHasKey('Kitsu', $urls);
		$this->assertArrayNotHasKey('Unknown Site', $urls);
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

	public function testGetPosterImage(): void
	{
		$data = [
			'posterImage' => ['original' => ['url' => 'poster.jpg']],
		];
		$this->assertEquals('poster.jpg', Kitsu::getPosterImage($data));
		$this->assertEquals('/public/images/placeholder.png', Kitsu::getPosterImage([]));
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

	public function testGetAiringStatus(): void
	{
		$this->assertEquals(AnimeAiringStatus::FINISHED_AIRING, Kitsu::getAiringStatus(
			'2020-01-01',
			'2020-12-31',
		));
		$this->assertEquals(AnimeAiringStatus::AIRING, Kitsu::getAiringStatus('2020-01-01', 'next year'));
		$this->assertEquals(AnimeAiringStatus::NOT_YET_AIRED, Kitsu::getAiringStatus('next year'));
	}

	public function testParseStreamingLinks(): void
	{
		$nodes = [
			[
				'url' => 'www.netflix.com/title/123',
				'subs' => ['en'],
				'dubs' => ['ja'],
			],
			[
				'url' => 'https://www.crunchyroll.com/watch/456',
				'subs' => ['en'],
				'dubs' => ['en'],
			],
		];
		$parsed = Kitsu::parseStreamingLinks($nodes);
		$this->assertCount(2, $parsed);
		$this->assertEquals('Crunchyroll', $parsed[0]['meta']['name']);
		$this->assertEquals('Netflix', $parsed[1]['meta']['name']);
	}
}
