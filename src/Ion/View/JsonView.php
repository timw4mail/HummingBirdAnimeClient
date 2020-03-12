<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.3
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
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