<?php

include_once __DIR__ . "/../ViewTest.php";

use Aviat\Ion\Friend;
use Aviat\Ion\View;
use Aviat\Ion\View\HtmlView;

class TestHtmlView extends HtmlView {
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

class HtmlViewTest extends ViewTest {

	protected $template_path;

	public function setUp()
	{
		parent::setUp();
		$this->template_path = __DIR__ . "/../../test_views/";
		$this->view = new TestHtmlView($this->container);
	}

	public function testRenderTemplate()
	{
		$path = $this->template_path . 'test_view.php';
		$expected = '<tag>foo</tag>';
		$actual = $this->view->render_template($path, [
			'var' => 'foo'
		]);
		$this->assertEquals($expected, $actual);
	}

}