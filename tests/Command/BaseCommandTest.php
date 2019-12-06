<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.2
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2019  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Tests\Command;

use Aviat\AnimeClient\Command\BaseCommand;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use Aviat\Ion\Friend;
use ConsoleKit\Console;

class BaseCommandTest extends AnimeClientTestCase {
	protected $base;
	protected $friend;

	public function setUp(): void	{
		$this->base = new BaseCommand(new Console());
		$this->friend = new Friend($this->base);
	}

	public function testSetupContainer()
	{
		$container = $this->friend->setupContainer();
		$this->assertInstanceOf('Aviat\Ion\Di\Container', $container);
	}
}