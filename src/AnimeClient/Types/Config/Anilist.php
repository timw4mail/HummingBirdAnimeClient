<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.4
 *
 * @copyright   2015 - 2026  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.3
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Types\Config;

use Aviat\AnimeClient\Types\AbstractType;

class Anilist extends AbstractType
{
	public bool|string $enabled = false;

	public null|string $client_id;

	public null|string $client_secret;

	public null|string $access_token;

	public int|string|null $access_token_expires;

	public null|string $refresh_token;

	public null|string $username;
}
