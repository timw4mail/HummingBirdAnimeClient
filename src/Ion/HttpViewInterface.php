<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2021  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\Ion;

/**
 * View Interface abstracting an HTTP Response
 */
interface HttpViewInterface extends ViewInterface {

	/**
	 * Set the status code of the request
	 *
	 * @param int $code
	 * @throws \InvalidArgumentException
	 * @return self
	 */
	public function setStatusCode(int $code): self;
}