<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshome.page>
 * @copyright   2015 - 2022  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\Ion\View;

use Aviat\Ion\Exception\DoubleRenderException;
use Aviat\Ion\HttpViewInterface;
use InvalidArgumentException;

use Laminas\Diactoros\Response;
use Laminas\HttpHandlerRunner\Emitter\SapiEmitter;
use Psr\Http\Message\ResponseInterface;
use Stringable;

/**
 * Base view class for Http output
 */
class HttpView implements HttpViewInterface, Stringable
{
	/**
	 * HTTP response Object
	 */
	public ResponseInterface $response;

	/**
	 * If the view has sent output via
	 * __toString or send method
	 */
	protected bool $hasRendered = FALSE;

	/**
	 * Response mime type
	 */
	protected string $contentType = '';

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->response = new Response();
	}

	/**
	 * Send output to client
	 */
	public function __destruct()
	{
		if ( ! $this->hasRendered)
		{
			$this->send();
		}
	}

	/**
	 * Return rendered output as string. Renders the view,
	 * and any attempts to call again will result in a DoubleRenderException
	 *
	 * @throws DoubleRenderException
	 */
	public function __toString(): string
	{
		if ($this->hasRendered)
		{
			throw new DoubleRenderException();
		}

		$this->hasRendered = TRUE;

		return $this->getOutput();
	}

	/**
	 * Add an http header
	 *
	 * @param string|string[] $value
	 */
	public function addHeader(string $name, array|string $value): self
	{
		$this->response = $this->response->withHeader($name, $value);

		return $this;
	}

	/**
	 * Set the output string
	 */
	public function setOutput(mixed $string): HttpViewInterface
	{
		$this->response->getBody()->write($string);

		return $this;
	}

	/**
	 * Append additional output.
	 */
	public function appendOutput(string $string): HttpViewInterface
	{
		return $this->setOutput($string);
	}

	/**
	 * Get the current output as a string. Does not
	 * render view or send headers.
	 */
	public function getOutput(): string
	{
		return (string) $this->response->getBody();
	}

	/**
	 * Do a redirect
	 *
	 * @throws InvalidArgumentException
	 */
	public function redirect(string $url, int $code = 302, array $headers = []): self
	{
		$this->response = new Response\RedirectResponse($url, $code, $headers);

		return $this;
	}

	/**
	 * Set the status code of the request
	 *
	 * @throws InvalidArgumentException
	 */
	public function setStatusCode(int $code): self
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
	 * @throws InvalidArgumentException
	 */
	public function send(): void
	{
		$this->output();
	}

	/**
	 * Send the appropriate response
	 *
	 * @codeCoverageIgnore
	 * @throws DoubleRenderException
	 * @throws InvalidArgumentException
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
