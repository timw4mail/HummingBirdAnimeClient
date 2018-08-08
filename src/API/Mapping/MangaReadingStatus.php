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

namespace Aviat\AnimeClient\API\Mapping;

use Aviat\AnimeClient\API\Enum\MangaReadingStatus\{Kitsu, MAL, Title, Route};
use Aviat\Ion\Enum;

/**
 * Manga reading status mappings, among Kitsu, MAL, Page titles
 * and url route segments
 */
final class MangaReadingStatus extends Enum {
	const KITSU_TO_MAL = [
		Kitsu::READING => MAL::READING,
		Kitsu::PLAN_TO_READ => MAL::PLAN_TO_READ,
		Kitsu::COMPLETED => MAL::COMPLETED,
		Kitsu::ON_HOLD => MAL::ON_HOLD,
		Kitsu::DROPPED => MAL::DROPPED
	];

	const MAL_TO_KITSU = [
		'1' => Kitsu::READING,
		'2' => Kitsu::COMPLETED,
		'3' => Kitsu::ON_HOLD,
		'4' => Kitsu::DROPPED,
		'6' => Kitsu::PLAN_TO_READ,
		MAL::READING => Kitsu::READING,
		MAL::COMPLETED => Kitsu::COMPLETED,
		MAL::ON_HOLD => Kitsu::ON_HOLD,
		MAL::DROPPED => Kitsu::DROPPED,
		MAL::PLAN_TO_READ => Kitsu::PLAN_TO_READ,
	];

	const KITSU_TO_TITLE = [
		Kitsu::READING => Title::READING,
		Kitsu::PLAN_TO_READ => Title::PLAN_TO_READ,
		Kitsu::COMPLETED => Title::COMPLETED,
		Kitsu::ON_HOLD => Title::ON_HOLD,
		Kitsu::DROPPED => Title::DROPPED,
	];

	const ROUTE_TO_KITSU = 	[
		Route::PLAN_TO_READ => Kitsu::PLAN_TO_READ,
		Route::READING => Kitsu::READING,
		Route::COMPLETED => Kitsu::COMPLETED,
		Route::DROPPED => Kitsu::DROPPED,
		Route::ON_HOLD => Kitsu::ON_HOLD,
	];

	const ROUTE_TO_TITLE = [
		Route::ALL => Title::ALL,
		Route::PLAN_TO_READ => Title::PLAN_TO_READ,
		Route::READING => Title::READING,
		Route::COMPLETED => Title::COMPLETED,
		Route::DROPPED => Title::DROPPED,
		Route::ON_HOLD => Title::ON_HOLD,
	];

	const TITLE_TO_KITSU = [
		Title::PLAN_TO_READ => Kitsu::PLAN_TO_READ,
		Title::READING => Kitsu::READING,
		Title::COMPLETED => Kitsu::COMPLETED,
		Title::DROPPED => Kitsu::DROPPED,
		Title::ON_HOLD => Kitsu::ON_HOLD,
	];
}