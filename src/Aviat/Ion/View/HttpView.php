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

use Aura\Web\ResponseSender;

use Aviat\Ion\View as BaseView;

/**
 * Base view class for Http output
 */
class HttpView extends BaseView {

	/**
	 * Do a redirect
	 *
	 * @param string $url
	 * @param int $code
	 * @return void
	 */
	public function redirect($url, $code)
	{
		$this->response->redirect->to($url, $code);
	}

	/**
	 * Set the status code of the request
	 *
	 * @param int $code
	 * @return HttpView
	 */
	public function setStatusCode($code)
	{
		$this->response->status->setCode($code);
		$this->response->status->setVersion(1.1);
		return $this;
	}

	/**
	 * Send output to client
	 */
	public function send()
	{
		$this->hasRendered = TRUE;
		$this->output();
	}

	/**
	 * Send the appropriate response
	 *
	 * @return void
	 */
	protected function output()
	{
		$content =& $this->response->content;
		$content->set($this->output);
		$content->setType($this->contentType);
		$content->setCharset('utf-8');

		$sender = new ResponseSender($this->response);
		$sender->__invoke();
	}

}