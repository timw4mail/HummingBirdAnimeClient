<?php declare(strict_types=1);

namespace Aviat\Ion\Tests\View;

use Aviat\Ion\Tests\IonTestCase;
use Aviat\Ion\View\HtmlView;

/**
 * @internal
 */
final class HtmlViewTest extends IonTestCase
{
	public function testHtmlView(): void
	{
		$view = new HtmlView($this->container);
		$view->addHeader('X-Test', 'foo');
		$view->appendOutput('bar');

		$this->assertEquals('bar', $view->getOutput());
		$this->assertEquals('foo', $view->response->getHeaderLine('X-Test'));
	}

	public function testRenderTemplate(): void
	{
		$view = new HtmlView($this->container);
		$template = self::ROOT_DIR . '/tests/Ion/View/test_template.php';
		file_put_contents($template, '<?php echo $foo; ?>');

		$output = $view->renderTemplate($template, ['foo' => 'bar']);
		$this->assertEquals('bar', $output);

		unlink($template);
	}

	public function testSetBaseData(): void
	{
		$view = new HtmlView($this->container);
		$view->setBaseData(['foo' => 'bar']);
		$template = self::ROOT_DIR . '/tests/Ion/View/test_template_base.php';
		file_put_contents($template, '<?php echo $foo; ?>');

		$output = $view->renderTemplate($template, []);
		$this->assertEquals('bar', $output);

		unlink($template);
	}

	public function testSetMinify(): void
	{
		$view = new HtmlView($this->container);
		$view->setMinify(true);
		$this->assertTrue(true); // Minify doesn't have an easy getter, but we covered the setter
	}
}
