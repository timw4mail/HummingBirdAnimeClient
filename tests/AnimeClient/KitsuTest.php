<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.1
 *
 * @copyright   2015 - 2023  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests\API;

use Aviat\AnimeClient\API\Kitsu\Enum\{AnimeAiringStatus, MangaPublishingStatus};
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
				'link' => TRUE,
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

	public static function getPublishingStatus(): array
	{
		return [
			'current' => [
				'kitsuStatus' => 'CURRENT',
				'expected' => MangaPublishingStatus::CURRENT,
			],
			'future' => [
				'kitsuStatus' => 'foo',
				'expected' => MangaPublishingStatus::NOT_YET_PUBLISHED,
			],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('getPublishingStatus')]
	public function testGetPublishingStatus(string $kitsuStatus, string $expected): void
	{
		$actual = Kitsu::getPublishingStatus($kitsuStatus);
		$this->assertSame($expected, $actual);
	}

	public function testFilterLocalizedTitles(): void
	{
		$input = [
			'canonical' => 'foo',
			'localized' => [
				'en' => 'Foo the Movie',
				'fr' => '',
				'jp' => NULL,
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
}
