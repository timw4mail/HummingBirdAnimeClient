<?php

use Aviat\Ion\Friend;

include_once __DIR__ . "/../ViewTest.php";

class JsonViewTest extends ViewTest {

	public function setUp()
	{
		parent::setUp();

		$this->view = new TestJsonView($this->container);
		$this->friend = new Friend($this->view);
	}

	public function testSetOutput()
	{
		// Extend view class to remove destructor which does output
		$view = new TestJsonView($this->container);

		// Json encode non-string
		$content = ['foo' => 'bar'];
		$expected = json_encode($content);
		$this->view->setOutput($content);
		$this->assertEquals($expected, $this->view->getOutput());

		// Directly set string
		$content = '{}';
		$expected = '{}';
		$this->view->setOutput($content);
		$this->assertEquals($expected, $this->view->getOutput());
	}

	public function testOutput()
	{
		$this->assertEquals('application/json', $this->friend->contentType);
	}
}