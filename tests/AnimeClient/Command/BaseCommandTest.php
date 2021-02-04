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

namespace Aviat\AnimeClient\Tests\Command;

use Aviat\AnimeClient\Command\BaseCommand;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use Aviat\Ion\Friend;
use ConsoleKit\Console;
use Aviat\Ion\Di\Container;

class Command extends BaseCommand {

}

class BaseCommandTest extends AnimeClientTestCase {
	protected $base;
	protected $friend;

	public function setUp(): void	{
		$this->base = new Command(new Console());
		$this->friend = new Friend($this->base);
	}

	public function testSetupContainer(): void
	{
		$container = $this->friend->setupContainer();
		$this->assertInstanceOf(Container::class, $container);
	}
}