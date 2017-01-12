<?php declare(strict_types=1);
/**
 * Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     AnimeListClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2017  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API;

use Aviat\AnimeClient\Enum\{AnimeWatchingStatus, MangaReadingStatus};

/**
 * Constants and mappings for the My Anime List API
 */
class MAL {
	const AUTH_URL = 'https://myanimelist.net/api/account/verify_credentials.xml';
	const BASE_URL = 'https://myanimelist.net/api/';

	public static function getIdToWatchingStatusMap()
	{
		return [
			1 => AnimeWatchingStatus::WATCHING,
			2 => AnimeWatchingStatus::COMPLETED,
			3 => AnimeWatchingStatus::ON_HOLD,
			4 => AnimeWatchingStatus::DROPPED,
			5 => AnimeWatchingStatus::PLAN_TO_WATCH
		];
	}

	public static function getIdToReadingStatusMap()
	{
		return [
			1 => MangaReadingStatus::READING,
			2 => MangaReadingStatus::COMPLETED,
			3 => MangaReadingStatus::ON_HOLD,
			4 => MangaReadingStatus::DROPPED,
			5 => MangaReadingStatus::PLAN_TO_READ
		];
	}
}