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

namespace Aviat\AnimeClient\Tests\Command;

use Aviat\AnimeClient\Command\BaseCommand;
use Aviat\AnimeClient\Tests\AnimeClient_TestCase;
use Aviat\Ion\Friend;
use ConsoleKit\Console;

class BaseCommandTest extends AnimeClient_TestCase {
	public function setUp()
	{
		$this->base = new BaseCommand(new Console());
		$this->friend = new Friend($this->base);
	}

	public function testSetupContainer()
	{
		$container = $this->friend->setupContainer();
		$this->assertInstanceOf('Aviat\Ion\Di\Container', $container);
	}
}