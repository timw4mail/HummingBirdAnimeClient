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

namespace Aviat\Ion\Tests\Di;

use Aviat\Ion\Di\{Container, ContainerAware};
use Aviat\Ion\Di\Exception\ContainerException;
use Aviat\Ion\Tests\IonTestCase;
use Monolog\Logger;
use Monolog\Handler\{TestHandler, NullHandler};
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Di\Exception\NotFoundException;
use Throwable;
use TypeError;

class FooTest {

	public $item;

	public function __construct($item) {
		$this->item = $item;
	}
}

class FooTest2 {
	use ContainerAware;
}

class ContainerTest extends IonTestCase {

	public function setUp(): void
	{
		$this->container = new Container();
	}

	public function dataGetWithException(): array
	{
		return [
			'Bad index type: number' => [
				'id' => 42,
				'exception' => TypeError::class,
			],
			'Bad index type: array' => [
				'id' => [],
				'exception' => TypeError::class,
			],
			'Non-existent id' => [
				'id' => 'foo',
				'exception' => NotFoundException::class,
				'message' => "Item 'foo' does not exist in container."
			]
		];
	}

	/**
	 * @dataProvider dataGetWithException
	 */
	public function testGetWithException(mixed $id, $exception, ?string $message = NULL): void
	{
		try
		{
			$this->container->get($id);
		}
		catch(ContainerException $e)
		{
			$this->assertInstanceOf($exception, $e);
			$this->assertEquals($message, $e->getMessage());
		}
		catch(Throwable $e)
		{
			$this->assertInstanceOf($exception, $e);
		}
	}

	/**
	 * @dataProvider dataGetWithException
	 */
	public function testGetNewWithException(mixed $id, $exception, ?string $message = NULL): void
	{
		$this->expectException($exception);
		if ($message !== NULL)
		{
			$this->expectExceptionMessage($message);
		}

		$this->container->getNew($id);
	}

	public function dataSetInstanceWithException(): array
	{
		return [
			'Non-existent id' => [
				'id' => 'foo',
				'exception' => NotFoundException::class,
				'message' => "Factory 'foo' does not exist in container. Set that first.",
			],
			'Non-existent id 2' => [
				'id' => 'foobarbaz',
				'exception' => NotFoundException::class,
				'message' => "Factory 'foobarbaz' does not exist in container. Set that first.",
			],
		];
	}

	/**
	 * @dataProvider dataSetInstanceWithException
	 */
	public function testSetInstanceWithException($id, $exception, $message): void
	{
		try
		{
			$this->container->setInstance($id, NULL);
		}
		catch(ContainerException $e)
		{
			$this->assertInstanceOf($exception, $e);
			$this->assertEquals($message, $e->getMessage());
		}
	}

	public function testGetNew(): void
	{
		$this->container->set('footest', static function($item) {
			return new FooTest($item);
		});

		// Check that the item is the container, if called without arguments
		$footest1 = $this->container->getNew('footest');
		$this->assertInstanceOf(ContainerInterface::class, $footest1->item);

		$footest2 = $this->container->getNew('footest', ['Test String']);
		$this->assertEquals('Test String', $footest2->item);
	}

	public function testSetContainerInInstance(): void
	{
		$this->container->set('footest2', function() {
			return new FooTest2();
		});

		$footest2 = $this->container->get('footest2');
		$this->assertEquals($this->container, $footest2->getContainer());
	}

	public function testGetNewReturnCallable(): void
	{
		$this->container->set('footest', static function($item) {
			return static function() use ($item) {
				return $item;
			};
		});

		// Check that the item is the container, if called without arguments
		$footest1 = $this->container->getNew('footest');
		$this->assertInstanceOf(ContainerInterface::class, $footest1());

		$footest2 = $this->container->getNew('footest', ['Test String']);
		$this->assertEquals('Test String', $footest2());
	}

	public function testGetSet(): void
	{
		$container = $this->container->set('foo', static function() {
			return static function() {};
		});

		$this->assertInstanceOf(Container::class, $container);
		$this->assertInstanceOf(ContainerInterface::class, $container);

		// The factory returns a callable
		$this->assertTrue(is_callable($container->get('foo')));
	}

	public function testLoggerMethods(): void
	{
		// Does the container have the default logger?
		$this->assertFalse($this->container->hasLogger());
		$this->assertFalse($this->container->hasLogger('default'));

		$logger1 = new Logger('default');
		$logger2 = new Logger('testing');
		$logger1->pushHandler(new NullHandler());
		$logger2->pushHandler(new TestHandler());

		// Set the logger channels
		$container = $this->container->setLogger($logger1);
		$container2 = $this->container->setLogger($logger2, 'test');

		$this->assertInstanceOf(ContainerInterface::class, $container);
		$this->assertInstanceOf(Container::class, $container2);

		$this->assertEquals($logger1, $this->container->getLogger('default'));
		$this->assertEquals($logger2, $this->container->getLogger('test'));
		$this->assertNull($this->container->getLogger('foo'));

		$this->assertTrue($this->container->hasLogger());
		$this->assertTrue($this->container->hasLogger('default'));
		$this->assertTrue($this->container->hasLogger('test'));
	}
}