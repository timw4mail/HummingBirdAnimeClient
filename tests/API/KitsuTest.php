<?php declare(strict_types=1);
/**
 * Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     AnimeListClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2017  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests\API;

use Aviat\AnimeClient\API\Kitsu;
use Aviat\AnimeClient\API\Kitsu\Enum\{
	AnimeAiringStatus,
	AnimeWatchingStatus,
	MangaReadingStatus
};
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class KitsuTest extends TestCase {
	public function testGetStatusToSelectMap()
	{
		$this->assertEquals([
			AnimeWatchingStatus::WATCHING => 'Currently Watching',
			AnimeWatchingStatus::PLAN_TO_WATCH => 'Plan to Watch',
			AnimeWatchingStatus::COMPLETED => 'Completed',
			AnimeWatchingStatus::ON_HOLD => 'On Hold',
			AnimeWatchingStatus::DROPPED => 'Dropped'
		], Kitsu::getStatusToSelectMap());
	}
	
	public function testGetStatusToMangaSelectMap()
	{
		$this->assertEquals([
			MangaReadingStatus::READING => 'Currently Reading',
			MangaReadingStatus::PLAN_TO_READ => 'Plan to Read',
			MangaReadingStatus::COMPLETED => 'Completed',
			MangaReadingStatus::ON_HOLD => 'On Hold',
			MangaReadingStatus::DROPPED => 'Dropped'
		], Kitsu::getStatusToMangaSelectMap());
	}
	
	public function testGetAiringStatus()
	{
		$actual = Kitsu::getAiringStatus('next week', 'next year');
		$this->assertEquals(AnimeAiringStatus::NOT_YET_AIRED, $actual);
	}
	
	public function testParseStreamingLinksEmpty()
	{
		$this->assertEquals([], Kitsu::parseStreamingLinks([]));
	}
	
	public function testTitleIsUniqueEmpty()
	{
		$actual = Kitsu::filterTitles([
			'canonicalTitle' => 'Foo',
			'titles' => [
				null,
				''
			]
		]);
		$this->assertEquals(['Foo'], $actual);
	}
}