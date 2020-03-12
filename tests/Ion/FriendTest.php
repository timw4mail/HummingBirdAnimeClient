<?php declare(strict_types=1);
/**
 * Ion
 *
 * Building blocks for web development
 *
 * PHP version 7.2
 *
 * @package     Ion
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2019 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     3.0.0
 * @link        https://git.timshomepage.net/aviat/ion
 */

namespace Aviat\Ion\Tests;

use Aviat\Ion\Friend;
use Aviat\Ion\Tests\FriendTestClass;

class FriendTest extends Ion_TestCase {

	public function setUp(): void	{
		parent::setUp();
		$obj = new FriendTestClass();
		$this->friend = new Friend($obj);
	}

	public function testPrivateMethod()
	{
		$actual = $this->friend->getPrivate();
		$this->assertEquals(23, $actual);
	}

	public function testProtectedMethod()
	{
		$actual = $this->friend->getProtected();
		$this->assertEquals(4, $actual);
	}

	public function testGet()
	{
		$this->assertEquals(356, $this->friend->protected);
		$this->assertNull($this->friend->foo); // Return NULL for non-existent properties
		$this->assertEquals(47, $this->friend->parentProtected);
		$this->assertEquals(84, $this->friend->grandParentProtected);
		$this->assertNull($this->friend->parentPrivate); // Can't get a parent's privates
	}

	public function testSet()
	{
		$this->friend->private = 123;
		$this->assertEquals(123, $this->friend->private);

		$this->friend->foo = 32;
		$this->assertNull($this->friend->foo);
	}

	public function testBadInvokation()
	{
		$this->expectException('InvalidArgumentException');
		$this->expectExceptionMessage('Friend must be an object');

		$friend = new Friend('foo');
	}

	public function testBadMethod()
	{
		$this->expectException('BadMethodCallException');
		$this->expectExceptionMessage("Method 'foo' does not exist");

		$this->friend->foo();
	}
}