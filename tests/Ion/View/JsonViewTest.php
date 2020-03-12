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
use Aviat\Ion\Tests\TestJsonView;

class JsonViewTest extends HttpViewTest {

	public function setUp(): void	{
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