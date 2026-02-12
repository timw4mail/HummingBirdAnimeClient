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

namespace Aviat\Ion\Tests;

use Aviat\Ion\Event;
use Aviat\Ion\Type\EventType;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class EventTest extends TestCase
{
	public function testEmit(): void
	{
		Event::on(EventType::TEST, fn (bool $fired) => $this->assertTrue($fired));
		Event::emit(EventType::TEST, [true]);
	}
}
