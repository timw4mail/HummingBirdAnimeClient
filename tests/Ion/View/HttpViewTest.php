<?php

include_once __DIR__ . "/../ViewTest.php";

use Aviat\Ion\Friend;
use Aviat\Ion\View\HttpView;

class TestHttpView extends HttpView {
	protected function output() {
		$reflect = new ReflectionClass($this);
		$properties = $reflect->getProperties();
		$props = [];

		foreach($properties as $reflectProp)
		{
			$reflectProp->setAccessible(TRUE);
			$props[$reflectProp->getName()] = $reflectProp->getValue($this);
		}

		$view = new TestView($this->container);
		$friend = new Friend($view);
		foreach($props as $name => $val)
		{
			$friend->__set($name, $val);
		}

		$friend->output();
	}
}

class HttpViewTest extends ViewTest {

	public function setUp()
	{
		parent::setUp();
		$this->view = new TestHttpView($this->container);
		$this->friend = new Friend($this->view);
	}

	public function testRedirect()
	{
		$this->friend->redirect('/foo', 303);
		$this->assertEquals('/foo', $this->friend->response->headers->get('Location'));
		$this->assertEquals(303, $this->friend->response->status->getCode());
	}
}