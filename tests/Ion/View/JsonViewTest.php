<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.1
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\Ion\Tests\View;

use Aviat\Ion\Friend;
use Aviat\Ion\Tests\TestJsonView;

class JsonViewTest extends HttpViewTest {

	public function setUp(): void	{
		parent::setUp();

		$this->view = new TestJsonView();
		$this->friend = new Friend($this->view);
	}

	public function testSetOutputJSON()
	{
		// Extend view class to remove destructor which does output
		$view = new TestJsonView();

		// Json encode non-string
		$content = ['foo' => 'bar'];
		$expected = json_encode($content);
		$view->setOutput($content);
		$this->assertEquals($expected, $view->getOutput());
	}

	public function testSetOutput()
	{
		// Directly set string
		$view = new TestJsonView();
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