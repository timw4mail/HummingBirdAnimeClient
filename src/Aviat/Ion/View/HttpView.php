<?php

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
	 * Send the appropriate response
	 *
	 * @return void
	 */
	protected function output()
	{
		parent::output();

		$sender = new ResponseSender($this->response);
		$sender->__invoke();
	}

}