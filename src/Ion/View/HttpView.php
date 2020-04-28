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
 * @version     5
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\Ion\View;

use Laminas\Diactoros\Response;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;

use Aviat\Ion\Exception\DoubleRenderException;
use Aviat\Ion\View as BaseView;

/**
 * Base view class for Http output
 */
class HttpView extends BaseView {

	/**
	 * Response mime type
	 *
	 * @var string
	 */
	protected string $contentType = '';

	/**
	 * Do a redirect
	 *
	 * @param string $url
	 * @param int    $code
	 * @param array $headers
	 * @throws \InvalidArgumentException
	 * @return void
	 */
	public function redirect(string $url, int $code = 302, array $headers = []): void
	{
		$this->response = new Response\RedirectResponse($url, $code, $headers);
	}

	/**
	 * Set the status code of the request
	 *
	 * @param int $code
	 * @throws \InvalidArgumentException
	 * @return HttpView
	 */
	public function setStatusCode(int $code): HttpView
	{
		$this->response = $this->response->withStatus($code)
			->withProtocolVersion('1.1');
		return $this;
	}

	/**
	 * Send output to client. As it renders the view,
	 * any attempt to call again will result in a DoubleRenderException.
	 *
	 * @throws DoubleRenderException
	 * @throws \InvalidArgumentException
	 * @return void
	 */
	public function send(): void
	{
		$this->output();
	}

	/**
	 * Send the appropriate response
	 *
	 * @throws DoubleRenderException
	 * @throws \InvalidArgumentException
	 * @return void
	 */
	protected function output(): void
	{
		if ($this->hasRendered)
		{
			throw new DoubleRenderException();
		}

		$this->response = $this->response
			->withHeader('Content-type', "{$this->contentType};charset=utf-8")
			->withHeader('X-Content-Type-Options', 'nosniff')
			->withHeader('X-XSS-Protection', '1;mode=block')
			->withHeader('X-Frame-Options', 'SAMEORIGIN');

		(new SapiEmitter())->emit($this->response);

		$this->hasRendered = TRUE;
	}
}