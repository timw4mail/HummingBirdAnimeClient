<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @copyright   2015 - 2022  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshome.page/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient;

use Aviat\AnimeClient\API\Enum\{
	AnimeWatchingStatus\Anilist as AnimeWatchingStatus,
	MangaReadingStatus\Anilist as MangaReadingStatus
};
use Aviat\AnimeClient\API\Enum\{
	AnimeWatchingStatus\Kitsu as KAWS,
	MangaReadingStatus\Kitsu as KMRS
};

/**
 * Constants and mappings for the Anilist API
 */
final class Anilist
{
	public const AUTH_URL = 'https://anilist.co/api/v2/oauth/authorize';
	public const TOKEN_URL = 'https://anilist.co/api/v2/oauth/token';
	public const BASE_URL = 'https://graphql.anilist.co';
	public const KITSU_ANILIST_WATCHING_STATUS_MAP = [
		KAWS::WATCHING => AnimeWatchingStatus::WATCHING,
		KAWS::COMPLETED => AnimeWatchingStatus::COMPLETED,
		KAWS::ON_HOLD => AnimeWatchingStatus::ON_HOLD,
		KAWS::DROPPED => AnimeWatchingStatus::DROPPED,
		KAWS::PLAN_TO_WATCH => AnimeWatchingStatus::PLAN_TO_WATCH,
	];
	public const ANILIST_KITSU_WATCHING_STATUS_MAP = [
		AnimeWatchingStatus::WATCHING => KAWS::WATCHING,
		AnimeWatchingStatus::COMPLETED => KAWS::COMPLETED,
		AnimeWatchingStatus::ON_HOLD => KAWS::ON_HOLD,
		AnimeWatchingStatus::DROPPED => KAWS::DROPPED,
		AnimeWatchingStatus::PLAN_TO_WATCH => KAWS::PLAN_TO_WATCH,
	];
	public const KITSU_ANILIST_READING_STATUS_MAP = [
		KMRS::READING => MangaReadingStatus::READING,
		KMRS::COMPLETED => MangaReadingStatus::COMPLETED,
		KMRS::ON_HOLD => MangaReadingStatus::ON_HOLD,
		KMRS::DROPPED => MangaReadingStatus::DROPPED,
		KMRS::PLAN_TO_READ => MangaReadingStatus::PLAN_TO_READ,
	];
	public const ANILIST_KITSU_READING_STATUS_MAP = [
		MangaReadingStatus::READING => KMRS::READING,
		MangaReadingStatus::COMPLETED => KMRS::COMPLETED,
		MangaReadingStatus::ON_HOLD => KMRS::ON_HOLD,
		MangaReadingStatus::DROPPED => KMRS::DROPPED,
		MangaReadingStatus::PLAN_TO_READ => KMRS::PLAN_TO_READ,
	];
}
