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

namespace Aviat\AnimeClient\Tests\Command;

use Aviat\AnimeClient\Command\BaseCommand;
use Aviat\AnimeClient\Tests\AnimeClientTestCase;
use Aviat\Ion\Di\Container;
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Friend;
use ConsoleKit\Console;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

class Command extends BaseCommand {}

/**
 * @internal
 */
#[AllowMockObjectsWithoutExpectations]
final class BaseCommandTest extends AnimeClientTestCase
{
	protected Command $base;

	protected Friend $friend;

	#[\Override]
	protected function setUp(): void
	{
		parent::setUp();
		$this->base = new Command(new Console());
		$this->friend = new Friend($this->base);
	}

	public function testSetupContainer(): void
	{
		$container = $this->friend->setupContainer();
		$this->assertInstanceOf(ContainerInterface::class, $container);
	}

	public function testEcho(): void
	{
		$console = $this->createMock(\ConsoleKit\Console::class);
		$console
			->expects($this->once())
			->method('writeln')
			->with('Test message');

		$base = new Command($console);
		$base->echo('Test message');
	}

	public function testEchoSuccess(): void
	{
		$console = $this->createMock(\ConsoleKit\Console::class);
		$console->expects($this->once())->method('writeln');

		$base = new Command($console);
		$base->echoSuccess('Test success');
	}

	public function testEchoWarning(): void
	{
		$console = $this->createMock(\ConsoleKit\Console::class);
		$console->expects($this->once())->method('writeln');

		$base = new Command($console);
		$base->echoWarning('Test warning');
	}

	public function testEchoError(): void
	{
		$console = $this->createMock(\ConsoleKit\Console::class);
		$console->expects($this->once())->method('writeln');

		$base = new Command($console);
		$base->echoError('Test error');
	}

	public function testEchoBox(): void
	{
		$console = $this->createMock(\ConsoleKit\Console::class);
		$console->method('write');

		$base = new Command($console);
		ob_start();
		$base->echoBox('Test box');
		ob_end_clean();
		$this->assertTrue(true);
	}

	public function testEchoWarningBox(): void
	{
		$console = $this->createMock(\ConsoleKit\Console::class);
		$console->method('write');

		$base = new Command($console);
		ob_start();
		$base->echoWarningBox('Test warning box');
		ob_end_clean();
		$this->assertTrue(true);
	}

	public function testEchoErrorBox(): void
	{
		$console = $this->createMock(\ConsoleKit\Console::class);
		$console->method('write');

		$base = new Command($console);
		ob_start();
		$base->echoErrorBox('Test error box');
		ob_end_clean();
		$this->assertTrue(true);
	}

	public function testClearLine(): void
	{
		$console = $this->createMock(\ConsoleKit\Console::class);
		$console->expects($this->once())->method('write')->with("\r\e[2K");

		$base = new Command($console);
		$base->clearLine();
	}

	public function testLine(): void
	{
		$console = $this->createMock(\ConsoleKit\Console::class);
		$console->expects($this->once())->method('writeln')->with('test');
		$base = new Command($console);
		$friend = new Friend($base);
		$friend->_line('test');
	}

	public function testDi(): void
	{
		$config = [
			'database' => [
				'type' => 'sqlite',
				'file' => ':memory:',
			],
		];
		$this->assertInstanceOf(ContainerInterface::class, $this->friend->_di(
			$config,
			self::ROOT_DIR . '/app',
		));
	}
}
