<?php

namespace Aviat\Ion\Base;

use Aura\Web\Request;
use Aura\Web\Response;

class Page {

	/**
	 * @var Request
	 */
	protected $request;

	/**
	 * @var Response
	 */
	protected $response;

	/**
	 * __construct function.
	 *
	 * @param Request $request
	 * @param Response $response
	 */
	public function __construct(Request $request, Response $response)
	{
		$this->request = $request;
		$this->response = $response;
	}

	/**
	 * __destruct function.
	 */
	public function __destruct()
	{
		$this->output();
	}

	/**
	 * Output the response to the client
	 */
	protected function output()
	{
		// send status
		@header($this->response->status->get(), true, $this->response->status->getCode());

		// headers
		foreach($this->response->headers->get() as $label => $value)
		{
			@header("{$label}: {$value}");
		}

		// cookies
		foreach($this->response->cookies->get() as $name => $cookie)
		{
			@setcookie(
				$name,
				$cookie['value'],
				$cookie['expire'],
				$cookie['path'],
				$cookie['domain'],
				$cookie['secure'],
				$cookie['httponly']
			);
		}

		// send the actual response
		echo $this->response->content->get();
	}
}
// End of Page.php