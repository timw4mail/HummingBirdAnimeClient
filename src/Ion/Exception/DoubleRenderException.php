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

namespace Aviat\Ion\Exception;

use Exception;
use LogicException;

/**
 * Exception called when a view is attempted to be sent twice
 */
class DoubleRenderException extends LogicException
{
	/**
	 * DoubleRenderException constructor.
	 */
	public function __construct(string $message = 'A view can only be rendered once, because headers can only be sent once.', int $code = 0, ?Exception $previous = NULL)
	{
		parent::__construct($message, $code, $previous);
	}
}
