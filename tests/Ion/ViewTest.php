<?php

use Aura\Web\WebFactory;
use Aviat\Ion\Friend;
use Aviat\Ion\View;
use Aviat\Ion\Di\Container;


class TestView extends View {

}

class ViewTest extends AnimeClient_TestCase {

	public function setUp()
	{
		parent::setUp();

		$web_factory = new WebFactory([
			'_GET' => $_GET,
			'_POST' => $_POST,
			'_COOKIE' => $_COOKIE,
			'_SERVER' => $_SERVER,
			'_FILES' => $_FILES
		]);
		$this->container->set('request', $web_factory->newRequest());
		$this->container->set('response', $web_factory->newResponse());

		$this->view = new TestView($this->container);
		$this->friend = new Friend($this->view);
	}

	public function testGetOutput()
	{
		$this->friend->output = 'foo';
		$this->assertEquals($this->friend->output, $this->friend->getOutput());
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
		$this->assertEquals($this->friend->response->content->getType(), $this->friend->contentType);
		$this->assertEquals($this->friend->response->content->getCharset(), 'utf-8');
		$this->assertEquals($this->friend->response->content->get(), $this->friend->getOutput());
	}
}