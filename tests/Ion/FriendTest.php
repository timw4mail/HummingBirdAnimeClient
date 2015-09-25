<?php

use Aviat\Ion\Friend;

class GrandParentTestClass {
	protected $grandParentProtected = 84;
}

class ParentTestClass extends GrandParentTestClass {
	protected $parentProtected = 47;
}

class TestClass extends ParentTestClass {
	protected $protected = 356;
	private $private = 486;

	protected function getProtected()
	{
		return 4;
	}

	private function getPrivate()
	{
		return 23;
	}
}

class FriendTest extends AnimeClient_TestCase {

	public function setUp()
	{
		parent::setUp();
		$obj = new TestClass();
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
		$this->assertNull($this->friend->foo);
		$this->assertEquals(47, $this->friend->parentProtected);
		$this->assertEquals(84, $this->friend->grandParentProtected);
	}

	public function testSet()
	{
		$this->friend->private = 123;
		$this->assertEquals(123, $this->friend->private);
	}

	public function testBadInvokation()
	{
		$this->setExpectedException('InvalidArgumentException', 'Friend must be an object');

		$friend = new Friend('foo');
	}

	public function testBadMethod()
	{
		$this->setExpectedException('BadMethodCallException', "Method 'foo' does not exist");
		$this->friend->foo();
	}
}