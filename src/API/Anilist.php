<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2018  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
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
	const AUTH_URL = 'https://anilist.co/api/v2/oauth/authorize';
	const BASE_URL = 'https://graphql.anilist.co';

	const KITSU_ANILIST_WATCHING_STATUS_MAP = [
		KAWS::WATCHING => AnimeWatchingStatus::WATCHING,
		KAWS::COMPLETED => AnimeWatchingStatus::COMPLETED,
		KAWS::ON_HOLD => AnimeWatchingStatus::ON_HOLD,
		KAWS::DROPPED => AnimeWatchingStatus::DROPPED,
		KAWS::PLAN_TO_WATCH => AnimeWatchingStatus::PLAN_TO_WATCH,
	];

	const ANILIST_KITSU_WATCHING_STATUS_MAP = [
		'CURRENT' => KAWS::WATCHING,
		'COMPLETED' => KAWS::COMPLETED,
		'PAUSED' => KAWS::ON_HOLD,
		'DROPPED' => KAWS::DROPPED,
		'PLANNING' => KAWS::PLAN_TO_WATCH,
	];

	public static function getIdToWatchingStatusMap()
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

	public static function getIdToReadingStatusMap()
	{
		return [
			'CURRENT' => MangaReadingStatus::READING,
			'COMPLETED' => MangaReadingStatus::COMPLETED,
			'PAUSED' => MangaReadingStatus::ON_HOLD,
			'DROPPED' => MangaReadingStatus::DROPPED,
			'PLANNING' => MangaReadingStatus::PLAN_TO_READ
		];
	}
}