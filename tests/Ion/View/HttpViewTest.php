<?php

use Aviat\Ion\Friend;

class HttpViewTest extends AnimeClient_TestCase {

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

	public function testGetOutput()
	{
		$this->friend->output = 'foo';
		$this->assertEquals($this->friend->output, $this->friend->getOutput());
		$this->assertFalse($this->friend->hasRendered);

		$this->assertEquals($this->friend->getOutput(), $this->friend->__toString());
		$this->assertTrue($this->friend->hasRendered);
	}

	public function testSetOutput()
	{
		$same = $this->view->setOutput('<h1></h1>');
		$this->assertEquals($same, $this->view);
		$this->assertEquals('<h1></h1>', $this->view->getOutput());
	}

	public function testAppendOutput()
	{
		$this->view->setOutput('<h1>');
		$this->view->appendOutput('</h1>');
		$this->assertEquals('<h1></h1>', $this->view->getOutput());
	}

	public function testOutput()
	{
		$this->friend->contentType = 'text/html';
		$this->friend->__destruct();
		$content =& $this->friend->response->content;
		$this->assertEquals($content->getType(), $this->friend->contentType);
		$this->assertEquals($content->getCharset(), 'utf-8');
		$this->assertEquals($content->get(), $this->friend->getOutput());
	}
}