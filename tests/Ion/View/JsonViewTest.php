<?php

use Aviat\Ion\Friend;

include_once __DIR__ . "/HttpViewTest.php";

class JsonViewTest extends HttpViewTest {

	public function setUp()
	{
		parent::setUp();

		$this->view = new TestJsonView($this->container);
		$this->friend = new Friend($this->view);
	}

	public function testSetOutputJSON()
	{
		// Extend view class to remove destructor which does output
		$view = new TestJsonView($this->container);

		// Json encode non-string
		$content = ['foo' => 'bar'];
		$expected = json_encode($content);
		$view->setOutput($content);
		$this->assertEquals($expected, $this->view->getOutput());
	}

	public function testSetOutput()
	{
		// Directly set string
		$view = new TestJsonView($this->container);
		$content = '{}';
		$expected = '{}';
		$view->setOutput($content);
		$this->assertEquals($expected, $view->getOutput());
	}

	public function testOutput()
	{
		$this->assertEquals('application/json', $this->friend->contentType);
	}
}