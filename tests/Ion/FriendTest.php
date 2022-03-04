<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshome.page>
 * @copyright   2015 - 2022  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\Ion\Tests;

use Aviat\Ion\Friend;

/**
 * @internal
 */
final class FriendTest extends IonTestCase
{
	protected $friend;

	protected function setUp(): void
	{
		parent::setUp();
		$obj = new FriendTestClass();
		$this->friend = new Friend($obj);
	}

	public function testPrivateMethod(): void
	{
		$actual = $this->friend->getPrivate();
		$this->assertSame(23, $actual);
	}

	public function testProtectedMethod(): void
	{
		$actual = $this->friend->getProtected();
		$this->assertSame(4, $actual);
	}

	public function testGet(): void
	{
		$this->assertSame(356, $this->friend->protected);
		$this->assertNull($this->friend->foo); // Return NULL for non-existent properties
		$this->assertSame(47, $this->friend->parentProtected);
		$this->assertSame(84, $this->friend->grandParentProtected);
		$this->assertNull($this->friend->parentPrivate); // Can't get a parent's privates
	}

	public function testSet(): void
	{
		$this->friend->private = 123;
		$this->assertSame(123, $this->friend->private);

		$this->friend->foo = 32;
		$this->assertNull($this->friend->foo);
	}

	public function testBadInvokation(): void
	{
		$this->expectException('InvalidArgumentException');
		$this->expectExceptionMessage('Friend must be an object');

		$friend = new Friend('foo');
	}

	public function testBadMethod(): void
	{
		$this->expectException('BadMethodCallException');
		$this->expectExceptionMessage("Method 'foo' does not exist");

		$this->friend->foo();
	}
}
