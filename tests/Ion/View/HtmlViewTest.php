<?php

include_once __DIR__ . "/HttpViewTest.php";

class HtmlViewTest extends HttpViewTest {

	protected $template_path;

	public function setUp()
	{
		parent::setUp();
		$this->view = new TestHtmlView($this->container);
	}

	public function testRenderTemplate()
	{
		$path = _dir(self::TEST_VIEW_DIR, 'test_view.php');
		$expected = '<tag>foo</tag>';
		$actual = $this->view->render_template($path, [
			'var' => 'foo'
		]);
		$this->assertEquals($expected, $actual);
	}

}