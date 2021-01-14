<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4+
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2021  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\Ion\Tests\View;

use function Aviat\Ion\_dir;

use Aviat\Ion\Tests\TestHtmlView;

class HtmlViewTest extends HttpViewTest {

	protected $template_path;

	public function setUp(): void	{
		parent::setUp();
		$this->view = new TestHtmlView($this->container);
	}

	public function testRenderTemplate(): void
	{
		$path = _dir(self::TEST_VIEW_DIR, 'test_view.php');
		$expected = '<tag>foo</tag>';
		$actual = $this->view->renderTemplate($path, [
			'var' => 'foo'
		]);
		$this->assertEquals($expected, $actual);
	}

}