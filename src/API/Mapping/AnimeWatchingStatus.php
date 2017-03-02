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
 * @copyright   2015 - 2017  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Mapping;
use Aviat\AnimeClient\API\Enum\AnimeWatchingStatus\{Route, Title};
use Aviat\AnimeClient\API\{
	Kitsu\Enum\AnimeWatchingStatus as Kitsu,
	MAL\Enum\AnimeWatchingStatus as MAL
};
use Aviat\Ion\Enum;

class AnimeWatchingStatus extends Enum {
	const MAL_TO_KITSU = [
		Kitsu::WATCHING => MAL::WATCHING,
		Kitsu::PLAN_TO_WATCH => MAL::PLAN_TO_WATCH,
		Kitsu::COMPLETED => MAL::COMPLETED,
		Kitsu::ON_HOLD => MAL::ON_HOLD,
		Kitsu::DROPPED => MAL::DROPPED
	];

	const KITSU_TO_MAL = [
		MAL::WATCHING => Kitsu::WATCHING,
		MAL::PLAN_TO_WATCH => Kitsu::PLAN_TO_WATCH,
		MAL::COMPLETED => Kitsu::COMPLETED,
		MAL::ON_HOLD => Kitsu::ON_HOLD,
		MAL::DROPPED => Kitsu::DROPPED
	];

	const KITSU_TO_TITLE = [
		'all' => Title::ALL,
		Kitsu::WATCHING => Title::WATCHING,
		Kitsu::PLAN_TO_WATCH => Title::PLAN_TO_WATCH,
		Kitsu::ON_HOLD => Title::ON_HOLD,
		Kitsu::DROPPED => Title::DROPPED,
		Kitsu::COMPLETED => Title::COMPLETED
	];

	const ROUTE_TO_KITSU = [
		Route::WATCHING => Kitsu::WATCHING,
		Route::PLAN_TO_WATCH => Kitsu::PLAN_TO_WATCH,
		Route::ON_HOLD => Kitsu::ON_HOLD,
		Route::ALL => 'all',
		Route::DROPPED => Kitsu::DROPPED,
		Route::COMPLETED => Kitsu::COMPLETED
	];

	const ROUTE_TO_TITLE = [
		Route::ALL => Title::ALL,
		Route::WATCHING => Title::WATCHING,
		Route::PLAN_TO_WATCH => Title::PLAN_TO_WATCH,
		Route::ON_HOLD => Title::ON_HOLD,
		Route::DROPPED => Title::DROPPED,
		Route::COMPLETED => Title::COMPLETED
	];

	const TITLE_TO_ROUTE = [
		Title::ALL => Route::ALL,
		Title::WATCHING => Route::WATCHING,
		Title::PLAN_TO_WATCH => Route::PLAN_TO_WATCH,
		Title::ON_HOLD => Route::ON_HOLD,
		Title::DROPPED => Route::DROPPED,
		Title::COMPLETED => Route::COMPLETED
	];
}
