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

namespace Aviat\AnimeClient\Tests\Command;

use Aviat\AnimeClient\Command\BaseCommand;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use Aviat\Ion\Di\Container;
use Aviat\Ion\Friend;
use ConsoleKit\Console;

class Command extends BaseCommand
{
}

/**
 * @internal
 */
final class BaseCommandTest extends AnimeClientTestCase
{
	protected Command $base;
	protected Friend $friend;

	protected function setUp(): void
	{
		$this->base = new Command(new Console());
		$this->friend = new Friend($this->base);
	}

	public function testSetupContainer(): void
	{
		$container = $this->friend->setupContainer();
		$this->assertInstanceOf(Container::class, $container);
	}
}
