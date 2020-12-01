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

namespace Aviat\AnimeClient\API\Mapping;

use Aviat\AnimeClient\API\Enum\AnimeWatchingStatus\{Anilist, Kitsu, Route, Title};
use Aviat\Ion\Enum;

/**
 * Anime watching status mappings, among Kitsu, MAL, Page titles
 * and url route segments
 */
final class AnimeWatchingStatus extends Enum {
	public const ANILIST_TO_KITSU = [
		Anilist::WATCHING => Kitsu::WATCHING,
		Anilist::PLAN_TO_WATCH => Kitsu::PLAN_TO_WATCH,
		Anilist::COMPLETED => Kitsu::COMPLETED,
		Anilist::ON_HOLD => Kitsu::ON_HOLD,
		Anilist::DROPPED => Kitsu::DROPPED
	];

	public const KITSU_TO_ANILIST = [
		Kitsu::WATCHING => Anilist::WATCHING,
		Kitsu::PLAN_TO_WATCH => Anilist::PLAN_TO_WATCH,
		Kitsu::COMPLETED => Anilist::COMPLETED,
		Kitsu::ON_HOLD => Anilist::ON_HOLD,
		Kitsu::DROPPED => Anilist::DROPPED
	];

	public const KITSU_TO_TITLE = [
		Kitsu::WATCHING => Title::WATCHING,
		Kitsu::PLAN_TO_WATCH => Title::PLAN_TO_WATCH,
		Kitsu::ON_HOLD => Title::ON_HOLD,
		Kitsu::DROPPED => Title::DROPPED,
		Kitsu::COMPLETED => Title::COMPLETED
	];

	public const ROUTE_TO_KITSU = [
		Route::WATCHING => Kitsu::WATCHING,
		Route::PLAN_TO_WATCH => Kitsu::PLAN_TO_WATCH,
		Route::ON_HOLD => Kitsu::ON_HOLD,
		Route::DROPPED => Kitsu::DROPPED,
		Route::COMPLETED => Kitsu::COMPLETED
	];

	public const ROUTE_TO_TITLE = [
		Route::ALL => Title::ALL,
		Route::WATCHING => Title::WATCHING,
		Route::PLAN_TO_WATCH => Title::PLAN_TO_WATCH,
		Route::ON_HOLD => Title::ON_HOLD,
		Route::DROPPED => Title::DROPPED,
		Route::COMPLETED => Title::COMPLETED
	];

	public const TITLE_TO_ROUTE = [
		Title::ALL => Route::ALL,
		Title::WATCHING => Route::WATCHING,
		Title::PLAN_TO_WATCH => Route::PLAN_TO_WATCH,
		Title::ON_HOLD => Route::ON_HOLD,
		Title::DROPPED => Route::DROPPED,
		Title::COMPLETED => Route::COMPLETED
	];
}
