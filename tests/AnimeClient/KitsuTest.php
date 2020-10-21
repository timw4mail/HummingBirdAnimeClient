<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.1
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests\API;

use Aviat\AnimeClient\Kitsu;
use Aviat\AnimeClient\API\Kitsu\Enum\AnimeAiringStatus;
use PHPUnit\Framework\TestCase;

class KitsuTest extends TestCase {
	public function testGetAiringStatus()
	{
		$actual = Kitsu::getAiringStatus('next week', 'next year');
		$this->assertEquals(AnimeAiringStatus::NOT_YET_AIRED, $actual);
	}

	public function testParseStreamingLinksEmpty()
	{
		$this->assertEquals([], Kitsu::parseStreamingLinks([]));
	}
}