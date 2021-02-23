<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2021  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests\API;

use Aviat\AnimeClient\API\Kitsu\Enum\MangaPublishingStatus;
use Aviat\AnimeClient\Kitsu;
use Aviat\AnimeClient\API\Kitsu\Enum\AnimeAiringStatus;
use PHPUnit\Framework\TestCase;

class KitsuTest extends TestCase {
	public function testGetAiringStatus(): void
	{
		$actual = Kitsu::getAiringStatus('next week', 'next year');
		$this->assertEquals(AnimeAiringStatus::NOT_YET_AIRED, $actual);
	}

	public function testParseStreamingLinksEmpty(): void
	{
		$this->assertEquals([], Kitsu::parseStreamingLinks([]));
	}

	public function testParseStreamingLinks(): void
	{
		$nodes = [[
			'url' => 'www.hulu.com/chobits',
			'dubs' => ['ja'],
			'subs' => ['en']
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

		$this->assertEquals([], Kitsu::parseStreamingLinks($nodes));
	}

	public function testGetAiringStatusEmptyArguments(): void
	{
		$this->assertEquals(AnimeAiringStatus::NOT_YET_AIRED, Kitsu::getAiringStatus());
	}

	public function testGetAiringStatusIsAiring(): void
	{
		$this->assertEquals(AnimeAiringStatus::AIRING, Kitsu::getAiringStatus('yesterday'));
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
			]
		];
	}

	/**
	 * @param string $kitsuStatus
	 * @param string $expected
	 * @dataProvider getPublishingStatus
	 */
	public function testGetPublishingStatus(string $kitsuStatus, string $expected): void
	{
		$actual = Kitsu::getPublishingStatus($kitsuStatus);
		$this->assertEquals($expected, $actual);
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
			'expected' => '2 years, 30 seconds'
		], [
			'seconds' => (5 * $SECONDS_IN_YEAR) + (3 * $SECONDS_IN_DAY) + (17 * Kitsu::SECONDS_IN_MINUTE),
			'expected' => '5 years, 3 days, and 17 minutes'
		]];
	}

	/**
	 * @param int $seconds
	 * @param string $expected
	 * @dataProvider getFriendlyTime
	 */
	public function testGetFriendlyTime(int $seconds, string $expected): void
	{
		$actual = Kitsu::friendlyTime($seconds);

		$this->assertEquals($expected, $actual);
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

		$this->assertEquals(['Foo the Movie'], $actual);
	}

	public function testGetFilteredTitles(): void
	{
		$input = [
			'canonical' => 'foo',
			'localized' => [
				'en' => 'Foo the Movie'
			],
			'alternatives' => [],
		];

		$actual = Kitsu::getFilteredTitles($input);

		$this->assertEquals(['Foo the Movie'], $actual);
	}
}