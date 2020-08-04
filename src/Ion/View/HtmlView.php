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

namespace Aviat\Ion\View;

use Aura\Html\HelperLocator;
use Aviat\Ion\Di\ContainerAware;
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Di\Exception\ContainerException;
use Aviat\Ion\Di\Exception\NotFoundException;
use Laminas\Diactoros\Response\HtmlResponse;
use const EXTR_OVERWRITE;

/**
 * View class for outputting HTML
 */
class HtmlView extends HttpView {
	use ContainerAware;

	/**
	 * HTML generator/escaper helper
	 *
	 * @var HelperLocator
	 */
	protected HelperLocator $helper;

	/**
	 * Response mime type
	 *
	 * @var string
	 */
	protected string $contentType = 'text/html';

	/**
	 * Create the Html View
	 *
	 * @param ContainerInterface $container
	 * @throws ContainerException
	 * @throws NotFoundException
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct();

		$this->setContainer($container);
		$this->helper = $container->get('html-helper');
		$this->response = new HtmlResponse('');
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
		extract($data, EXTR_OVERWRITE);
		include_once $path;
		$buffer = ob_get_clean();


		// Very basic html minify, that won't affect content between html tags
		$buffer = preg_replace('/>\s+</', '> <', $buffer);

		return $buffer;
	}
}
// End of HtmlView.php