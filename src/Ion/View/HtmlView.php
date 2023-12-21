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

namespace Aviat\Ion\View;

use Aviat\Ion\Di\ContainerAware;
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
	 * Data to send to every template
	 */
	protected array $baseData = [];

	/**
	 * Response mime type
	 */
	protected string $contentType = 'text/html';

	/**
	 * Whether to 'minify' the html output
	 */
	protected bool $shouldMinify = false;

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
	 * Set data to pass to every template
	 *
	 * @param array $data - Keys are variable names
	 */
	public function setBaseData(array $data): self
	{
		$this->baseData = $data;

		return $this;
	}

	/**
	 * Should the html be 'minified'?
	 */
	public function setMinify(bool $shouldMinify): self
	{
		$this->shouldMinify = $shouldMinify;

		return $this;
	}

	/**
	 * Render a basic html Template
	 *
	 * @throws Throwable
	 */
	public function renderTemplate(string $path, array $data = []): string
	{
		$data = array_merge($this->baseData, $data);

		return (function () use ($data, $path) {
			ob_start();
			extract($data, EXTR_OVERWRITE);
			include_once $path;
			$rawBuffer = ob_get_clean();
			$buffer = ($rawBuffer === FALSE) ? '' : $rawBuffer;

			// Very basic html minify, that won't affect content between html tags
			if ($this->shouldMinify)
			{
				$buffer = preg_replace('/>\s+</', '> <', $buffer) ?? $buffer;
			}
			return $buffer;
		})();
	}
}

// End of HtmlView.php
