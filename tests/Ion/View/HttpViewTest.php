<?php

include_once __DIR__ . "/../ViewTest.php";

use Aviat\Ion\Friend;

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