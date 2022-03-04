<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshome.page>
 * @copyright   2015 - 2022  Timothy J. Warren
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

	public function getPublishingStatus(): array
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

	/**
	 * @dataProvider getPublishingStatus
	 */
	public function testGetPublishingStatus(string $kitsuStatus, string $expected): void
	{
		$actual = Kitsu::getPublishingStatus($kitsuStatus);
		$this->assertSame($expected, $actual);
	}

	public function getFriendlyTime(): array
	{
		$SECONDS_IN_DAY = Kitsu::SECONDS_IN_MINUTE * Kitsu::MINUTES_IN_DAY;
		$SECONDS_IN_HOUR = Kitsu::SECONDS_IN_MINUTE * Kitsu::MINUTES_IN_HOUR;
		$SECONDS_IN_YEAR = Kitsu::SECONDS_IN_MINUTE * Kitsu::MINUTES_IN_YEAR;

		return [[
			'seconds' => $SECONDS_IN_YEAR,
			'expected' => '1 year',
		], [
			'seconds' => $SECONDS_IN_HOUR,
			'expected' => '1 hour',
		], [
			'seconds' => (2 * $SECONDS_IN_YEAR) + 30,
			'expected' => '2 years, 30 seconds',
		], [
			'seconds' => (5 * $SECONDS_IN_YEAR) + (3 * $SECONDS_IN_DAY) + (17 * Kitsu::SECONDS_IN_MINUTE),
			'expected' => '5 years, 3 days, and 17 minutes',
		]];
	}

	/**
	 * @dataProvider getFriendlyTime
	 */
	public function testGetFriendlyTime(int $seconds, string $expected): void
	{
		$actual = Kitsu::friendlyTime($seconds);

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
