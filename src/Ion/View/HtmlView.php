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

namespace Aviat\Ion\View;

use Aura\Html\HelperLocator;
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Di\Exception\ContainerException;
use Aviat\Ion\Di\Exception\NotFoundException;

/**
 * View class for outputting HTML
 */
class HtmlView extends HttpView {

	/**
	 * HTML generator/escaper helper
	 *
	 * @var HelperLocator
	 */
	protected $helper;

	/**
	 * Response mime type
	 *
	 * @var string
	 */
	protected $contentType = 'text/html';

	/**
	 * Create the Html View
	 *
	 * @param ContainerInterface $container
	 * @throws ContainerException
	 * @throws NotFoundException
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);
		$this->helper = $container->get('html-helper');
	}

	/**
	 * Render a basic html Template
	 *
	 * @param string $path
	 * @param array  $data
	 * @return string
	 */
	public function renderTemplate(string $path, array $data): string
	{
		$data['helper'] = $this->helper;
		$data['escape'] = $this->helper->escape();
		$data['container'] = $this->container;

		ob_start();
		extract($data, \EXTR_OVERWRITE);
		include_once $path;
		$buffer = ob_get_clean();


		// Very basic html minify, that won't affect content between html tags
		$buffer = preg_replace('/>\s+</', '> <', $buffer);

		return $buffer;
	}
}
// End of HtmlView.php