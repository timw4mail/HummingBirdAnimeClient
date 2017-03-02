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

	const ROUTE_TO_KITSU = [
		'watching' => Kitsu::WATCHING,
		'plan_to_watch' => Kitsu::PLAN_TO_WATCH,
		'on_hold' => Kitsu::ON_HOLD,
		'all' => 'all',
		'dropped' => Kitsu::DROPPED,
		'completed' => Kitsu::COMPLETED
	];

	const ROUTE_TO_TITLE = [
		'all' => 'All',
		'watching' => 'Currently Watching',
		'plan_to_watch' => 'Plan to Watch',
		'on_hold' => 'On Hold',
		'dropped' => 'Dropped',
		'completed' => 'Completed'
	];

	const KITSU_TO_TITLE = [
		Kitsu::WATCHING => 'Currently Watching',
		Kitsu::PLAN_TO_WATCH => 'Plan to Watch',
		Kitsu::ON_HOLD => 'On Hold',
		Kitsu::DROPPED => 'Dropped',
		Kitsu::COMPLETED => 'Completed'
	];
}