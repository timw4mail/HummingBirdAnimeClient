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

namespace Aviat\Ion\View;

use Aviat\Ion\Di\{ContainerAware, ContainerInterface};
use Laminas\Diactoros\Response\HtmlResponse;
use Throwable;
use const EXTR_OVERWRITE;

/**
 * View class for outputting HTML
 */
class HtmlView extends HttpView
{
	use ContainerAware;

	/**
	 * Response mime type
	 */
	protected string $contentType = 'text/html';

	/**
	 * Create the Html View
	 */
	public function __construct()
	{
		parent::__construct();

		$this->setContainer(func_get_arg(0));
		$this->response = new HtmlResponse('');
	}

	/**
	 * Render a basic html Template
	 *
	 * @throws Throwable
	 */
	public function renderTemplate(string $path, array $data): string
	{
		$helper = $this->container->get('html-helper');
		$data['component'] = $this->container->get('component-helper');
		$data['helper'] = $helper;
		$data['escape'] = $helper->escape();
		$data['container'] = $this->container;

		ob_start();
		extract($data, EXTR_OVERWRITE);
		include_once $path;
		$rawBuffer = ob_get_clean();
		$buffer = ($rawBuffer === FALSE) ? '' : $rawBuffer;

		// Very basic html minify, that won't affect content between html tags
		return preg_replace('/>\s+</', '> <', $buffer) ?? $buffer;
	}
}

// End of HtmlView.php
