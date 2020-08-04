<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.1
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Types\Config;

use Aviat\AnimeClient\Types\AbstractType;

class Anilist extends AbstractType {
	/**
	 * @var bool
	 */
	public $enabled = FALSE;

	/**
	 * @var string
	 */
	public $client_id;

	/**
	 * @var string
	 */
	public $client_secret;

	/**
	 * @var string
	 */
	public $access_token;

	/**
	 * @var string
	 */
	public $access_token_expires;

	/**
	 * @var string
	 */
	public $refresh_token;

	/**
	 * @var string
	 */
	public $username;
}