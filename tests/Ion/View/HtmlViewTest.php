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

use function Aviat\Ion\_dir;

use Aviat\Ion\Tests\TestHtmlView;

class HtmlViewTest extends HttpViewTest {

	protected $template_path;

	public function setUp(): void	{
		parent::setUp();
		$this->view = new TestHtmlView($this->container);
	}

	public function testRenderTemplate()
	{
		$path = _dir(self::TEST_VIEW_DIR, 'test_view.php');
		$expected = '<tag>foo</tag>';
		$actual = $this->view->renderTemplate($path, [
			'var' => 'foo'
		]);
		$this->assertEquals($expected, $actual);
	}

}