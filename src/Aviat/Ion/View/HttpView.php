<?php
/**
 * Ion
 *
 * Building blocks for web development
 *
 * @package     Ion
 * @author      Timothy J. Warren
 * @copyright   Copyright (c) 2015 - 2016
 * @license     MIT
 */

namespace Aviat\Ion\View;

use Zend\Diactoros\Response;
use Zend\Diactoros\Response\SapiEmitter;
use Aviat\Ion\View as BaseView;

/**
 * Base view class for Http output
 */
class HttpView extends BaseView {

	/**
	 * Do a redirect
	 *
	 * @codeCoverageIgnore
	 * @param string $url
	 * @param int $code
	 * @return void
	 */
	public function redirect($url, $code)
	{
		ob_start();
		$message = $this->response->getReasonPhrase($code);
		$this->setStatusCode($code);
		$this->response->withHeader('Location', $url);

		if (PHP_SAPI !== 'cli')
		{
			header("HTTP/1.1 ${code} ${message}");
			header("Location: {$url}");
		}

		$this->hasRendered = TRUE;
		ob_end_clean();
	}

	/**
	 * Set the status code of the request
	 *
	 * @param int $code
	 * @return HttpView
	 */
	public function setStatusCode($code)
	{
		$this->response = $this->response->withStatus($code)
			->withProtocolVersion('1.1');
		return $this;
	}

	/**
	 * Send output to client
	 *
	 * @return void
	 */
	public function send()
	{
		$this->hasRendered = TRUE;
		$this->output();
	}

	/**
	 * Send the appropriate response
	 *
	 * @codeCoverageIgnore
	 * @return void
	 */
	protected function output()
	{
		$this->response->withHeader('Content-type', "{$this->contentType};charset=utf-8")
			->withHeader('Content-Security-Policy', "script-src 'self'")
			->withHeader('X-Content-Type-Options', 'nosniff')
			->withHeader('X-XSS-Protection', '1;mode=block')
			->withHeader('X-Frame-Options', 'SAMEORIGIN');

		$sender = new SapiEmitter($this->response);
		$sender->emit($this->response);
	}

}