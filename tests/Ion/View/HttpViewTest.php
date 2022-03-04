<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @copyright   2015 - 2022  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshome.page/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\Ion\Tests\View;

use Aviat\Ion\Exception\DoubleRenderException;
use Aviat\Ion\Friend;
use Aviat\Ion\Tests\{IonTestCase, TestHttpView};

class HttpViewTest extends IonTestCase
{
	protected $view;
	protected $friend;

	protected function setUp(): void
	{
		parent::setUp();
		$this->view = new TestHttpView();
		$this->friend = new Friend($this->view);
	}

	public function testGetOutput(): void
	{
		$this->friend->setOutput('foo');
		$this->assertSame('foo', $this->friend->getOutput());
		$this->assertFalse($this->friend->hasRendered);

		$this->assertSame($this->friend->getOutput(), $this->friend->__toString());
		$this->assertTrue($this->friend->hasRendered);
	}

	public function testSetOutput(): void
	{
		$same = $this->view->setOutput('<h1></h1>');
		$this->assertSame($same, $this->view);
		$this->assertSame('<h1></h1>', $this->view->getOutput());
	}

	public function testAppendOutput(): void
	{
		$this->view->setOutput('<h1>');
		$this->view->appendOutput('</h1>');
		$this->assertSame('<h1></h1>', $this->view->getOutput());
	}

	public function testSetStatusCode(): void
	{
		$view = $this->view->setStatusCode(404);
		$this->assertSame(404, $view->response->getStatusCode());
	}

	public function testAddHeader(): void
	{
		$view = $this->view->addHeader('foo', 'bar');
		$this->assertTrue($view->response->hasHeader('foo'));
		$this->assertSame(['bar'], $view->response->getHeader('foo'));
	}

	public function testSendDoubleRenderException(): void
	{
		$this->expectException(DoubleRenderException::class);
		$this->expectExceptionMessage('A view can only be rendered once, because headers can only be sent once.');

		// First render
		$this->view->__toString();

		// Second render
		$this->view->send();
	}

	public function testToStringDoubleRenderException(): void
	{
		$this->expectException(DoubleRenderException::class);
		$this->expectExceptionMessage('A view can only be rendered once, because headers can only be sent once.');

		// First render
		$this->view->send();

		// Second render
		$this->view->__toString();
	}

	public function testRedirect(): void
	{
		$this->friend->redirect('http://example.com');
		$this->assertInstanceOf(\Laminas\Diactoros\Response\RedirectResponse::class, $this->friend->response);
	}

	public function testOutput(): void
	{
		$this->friend->setOutput('<h1></h1>');
		$this->friend->send();

		$this->assertTrue($this->friend->hasRendered);
	}
}
