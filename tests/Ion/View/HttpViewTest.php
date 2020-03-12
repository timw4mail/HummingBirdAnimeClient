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

namespace Aviat\Ion\Tests\View;

use Aviat\Ion\Friend;
use Aviat\Ion\Exception\DoubleRenderException;
use Aviat\Ion\Tests\Ion_TestCase;
use Aviat\Ion\Tests\TestHttpView;

class HttpViewTest extends Ion_TestCase {

	protected $view;
	protected $friend;

	public function setUp(): void	{
		parent::setUp();
		$this->view = new TestHttpView($this->container);
		$this->friend = new Friend($this->view);
	}

	public function testGetOutput()
	{
		$this->friend->setOutput('foo');
		$this->assertEquals('foo', $this->friend->getOutput());
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

	public function testSetStatusCode()
	{
		$view = $this->view->setStatusCode(404);
		$this->assertEquals(404, $view->response->getStatusCode());
	}

	public function testAddHeader()
	{
		$view = $this->view->addHeader('foo', 'bar');
		$this->assertTrue($view->response->hasHeader('foo'));
		$this->assertEquals(['bar'], $view->response->getHeader('foo'));
	}

	public function testSendDoubleRenderException()
	{
		$this->expectException(DoubleRenderException::class);
		$this->expectExceptionMessage('A view can only be rendered once, because headers can only be sent once.');

		// First render
		$this->view->__toString();

		// Second render
		$this->view->send();
	}

	public function test__toStringDoubleRenderException()
	{
		$this->expectException(DoubleRenderException::class);
		$this->expectExceptionMessage('A view can only be rendered once, because headers can only be sent once.');

		// First render
		$this->view->send();

		// Second render
		$this->view->__toString();
	}
}