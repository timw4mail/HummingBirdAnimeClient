<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4+
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\Ion\Tests;

use Aviat\Ion\Friend;
use Aviat\Ion\Tests\FriendTestClass;

class FriendTest extends IonTestCase {

	protected $friend;

	public function setUp(): void	{
		parent::setUp();
		$obj = new FriendTestClass();
		$this->friend = new Friend($obj);
	}

	public function testPrivateMethod():void
	{
		$actual = $this->friend->getPrivate();
		$this->assertEquals(23, $actual);
	}

	public function testProtectedMethod():void
	{
		$actual = $this->friend->getProtected();
		$this->assertEquals(4, $actual);
	}

	public function testGet():void
	{
		$this->assertEquals(356, $this->friend->protected);
		$this->assertNull($this->friend->foo); // Return NULL for non-existent properties
		$this->assertEquals(47, $this->friend->parentProtected);
		$this->assertEquals(84, $this->friend->grandParentProtected);
		$this->assertNull($this->friend->parentPrivate); // Can't get a parent's privates
	}

	public function testSet(): void
	{
		$this->friend->private = 123;
		$this->assertEquals(123, $this->friend->private);

		$this->friend->foo = 32;
		$this->assertNull($this->friend->foo);
	}

	public function testBadInvokation():void
	{
		$this->expectException('InvalidArgumentException');
		$this->expectExceptionMessage('Friend must be an object');

		$friend = new Friend('foo');
	}

	public function testBadMethod():void
	{
		$this->expectException('BadMethodCallException');
		$this->expectExceptionMessage("Method 'foo' does not exist");

		$this->friend->foo();
	}
}