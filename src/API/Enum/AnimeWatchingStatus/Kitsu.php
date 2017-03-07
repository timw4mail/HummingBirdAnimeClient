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

namespace Aviat\AnimeClient\API\Enum\AnimeWatchingStatus;

use Aviat\Ion\Enum;

/**
 * Possible values for watching status for the current anime
 */
class Kitsu extends Enum {
	const WATCHING = 'current';
	const PLAN_TO_WATCH = 'planned';
	const COMPLETED = 'completed';
	const ON_HOLD = 'on_hold';
	const DROPPED = 'dropped';
}