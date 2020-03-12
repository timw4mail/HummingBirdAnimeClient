<?php declare(strict_types=1);
/**
 * Ion
 *
 * Building blocks for web development
 *
 * PHP version 7.2
 *
 * @package     Ion
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2019 Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     3.0.0
 * @link        https://git.timshomepage.net/aviat/ion
 */

namespace Aviat\Ion\View;

use Aviat\Ion\Json;
use Aviat\Ion\JsonException;
use Aviat\Ion\ViewInterface;

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
	 * @throws \InvalidArgumentException
	 * @throws \RuntimeException
	 * @throws JsonException
	 * @return ViewInterface
	 */
	public function setOutput($string): ViewInterface
	{
		if ( ! \is_string($string))
		{
			$string = Json::encode($string);
		}

		return parent::setOutput($string);
	}
}
// End of JsonView.php