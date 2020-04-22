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
 * @version     5
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API;

use Aviat\AnimeClient\API\Enum\{
	AnimeWatchingStatus\Kitsu as KAWS,
	MangaReadingStatus\Kitsu as KMRS
};
use Aviat\AnimeClient\API\Enum\{
	AnimeWatchingStatus\Anilist as AnimeWatchingStatus,
	MangaReadingStatus\Anilist as MangaReadingStatus
};

/**
 * Constants and mappings for the Anilist API
 */
final class Anilist {
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

	public static function getIdToWatchingStatusMap(): array
	{
		return [
			'CURRENT' => AnimeWatchingStatus::WATCHING,
			'COMPLETED' => AnimeWatchingStatus::COMPLETED,
			'PAUSED' => AnimeWatchingStatus::ON_HOLD,
			'DROPPED' => AnimeWatchingStatus::DROPPED,
			'PLANNING' => AnimeWatchingStatus::PLAN_TO_WATCH,
			'REPEATING' => AnimeWatchingStatus::WATCHING,
		];
	}

	public static function getIdToReadingStatusMap(): array
	{
		return [
			'CURRENT' => MangaReadingStatus::READING,
			'COMPLETED' => MangaReadingStatus::COMPLETED,
			'PAUSED' => MangaReadingStatus::ON_HOLD,
			'DROPPED' => MangaReadingStatus::DROPPED,
			'PLANNING' => MangaReadingStatus::PLAN_TO_READ,
			'REPEATING' => MangaReadingStatus::READING,
		];
	}
}