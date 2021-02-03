<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4+
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2021  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Types\Config;

use Aviat\AnimeClient\Types\AbstractType;

class Anilist extends AbstractType {
	/**
	 * @var bool|string
	 */
	public $enabled = FALSE;

	public ?string $client_id;

	public ?string $client_secret;

	public ?string $access_token;

	/**
	 * @var int|string|null
	 */
	public $access_token_expires;

	public ?string $refresh_token;

	public ?string $username;
}