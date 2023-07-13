<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.1
 *
 * @copyright   2015 - 2023  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\Ion\Tests\View;

use Aviat\Ion\Friend;
use Aviat\Ion\Tests\TestJsonView;

/**
 * @internal
 */
final class JsonViewTest extends HttpViewTest
{
	protected function setUp(): void
	{
		parent::setUp();

		$this->view = new TestJsonView();
		$this->friend = new Friend($this->view);
	}

	public function testSetOutputJSON(): void
	{
		// Extend view class to remove destructor which does output
		$view = new TestJsonView();

		// Json encode non-string
		$content = ['foo' => 'bar'];
		$expected = json_encode($content);
		$view->setOutput($content);
		$this->assertSame($expected, $view->getOutput());
	}

	public function testSetOutput(): void
	{
		// Directly set string
		$view = new TestJsonView();
		$content = '{}';
		$expected = '{}';
		$view->setOutput($content);
		$this->assertSame($expected, $view->getOutput());
	}

	public function testOutputType(): void
	{
		$this->assertSame('application/json', $this->friend->contentType);
	}
}
