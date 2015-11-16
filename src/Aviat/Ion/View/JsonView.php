<?php
/**
 * Ion
 *
 * Building blocks for web development
 *
 * @package     Ion
 * @author      Timothy J. Warren
 * @copyright   Copyright (c) 2015
 * @license     MIT
 */

namespace Aviat\Ion\View;

use Aviat\Ion\View\HttpView;

/**
 * View class to serialize Json
 */
class JsonView extends HttpView {

	/**
	 * Response mime type
	 *
	 * @var string
	 */
	protected $contentType = 'application/json';

	/**
	 * Set the output string
	 *
	 * @param mixed $string
	 * @return View
	 */
	public function setOutput($string)
	{
		if ( ! is_string($string))
		{
			$string = json_encode($string);
		}

		return parent::setOutput($string);
	}
}
// End of JsonView.php