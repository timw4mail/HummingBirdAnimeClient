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

namespace Aviat\AnimeClient\Types\Config;

use Aviat\AnimeClient\Types\AbstractType;

class Anilist extends AbstractType
{
	public bool|string $enabled = FALSE;
	public ?string $client_id;
	public ?string $client_secret;
	public ?string $access_token;
	public int|string|NULL $access_token_expires;
	public ?string $refresh_token;
	public ?string $username;
}
