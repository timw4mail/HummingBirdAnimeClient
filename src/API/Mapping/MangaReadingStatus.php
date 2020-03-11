<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.3
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Mapping;

use Aviat\AnimeClient\API\Enum\MangaReadingStatus\{Anilist, Kitsu, Title, Route};
use Aviat\Ion\Enum;

/**
 * Manga reading status mappings, among Kitsu, MAL, Page titles
 * and url route segments
 */
final class MangaReadingStatus extends Enum {
	public const ANILIST_TO_KITSU = [
		Anilist::READING => Kitsu::READING,
		Anilist::PLAN_TO_READ => Kitsu::PLAN_TO_READ,
		Anilist::COMPLETED => Kitsu::COMPLETED,
		Anilist::ON_HOLD => Kitsu::ON_HOLD,
		Anilist::DROPPED => Kitsu::DROPPED
	];

	public const KITSU_TO_ANILIST = [
		Kitsu::READING => Anilist::READING,
		Kitsu::PLAN_TO_READ => Anilist::PLAN_TO_READ,
		Kitsu::COMPLETED => Anilist::COMPLETED,
		Kitsu::ON_HOLD => Anilist::ON_HOLD,
		Kitsu::DROPPED => Anilist::DROPPED
	];

	public const KITSU_TO_TITLE = [
		Kitsu::READING => Title::READING,
		Kitsu::PLAN_TO_READ => Title::PLAN_TO_READ,
		Kitsu::COMPLETED => Title::COMPLETED,
		Kitsu::ON_HOLD => Title::ON_HOLD,
		Kitsu::DROPPED => Title::DROPPED,
	];

	public const ROUTE_TO_KITSU = 	[
		Route::PLAN_TO_READ => Kitsu::PLAN_TO_READ,
		Route::READING => Kitsu::READING,
		Route::COMPLETED => Kitsu::COMPLETED,
		Route::DROPPED => Kitsu::DROPPED,
		Route::ON_HOLD => Kitsu::ON_HOLD,
	];

	public const ROUTE_TO_TITLE = [
		Route::ALL => Title::ALL,
		Route::PLAN_TO_READ => Title::PLAN_TO_READ,
		Route::READING => Title::READING,
		Route::COMPLETED => Title::COMPLETED,
		Route::DROPPED => Title::DROPPED,
		Route::ON_HOLD => Title::ON_HOLD,
	];

	public const TITLE_TO_KITSU = [
		Title::PLAN_TO_READ => Kitsu::PLAN_TO_READ,
		Title::READING => Kitsu::READING,
		Title::COMPLETED => Kitsu::COMPLETED,
		Title::DROPPED => Kitsu::DROPPED,
		Title::ON_HOLD => Kitsu::ON_HOLD,
	];
}