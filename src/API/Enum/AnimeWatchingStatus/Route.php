<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
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

namespace Aviat\AnimeClient\API\Enum\AnimeWatchingStatus;

use Aviat\Ion\Enum as Enum;

/**
 * Possible values for current watching status of anime
 */
final class Route extends Enum {
	const ALL = 'all';
	const WATCHING = 'watching';
	const PLAN_TO_WATCH = 'plan_to_watch';
	const DROPPED = 'dropped';
	const ON_HOLD = 'on_hold';
	const COMPLETED = 'completed';
}
