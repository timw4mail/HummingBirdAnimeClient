<?php
/**
 * Hummingbird Anime Client
 *
 * An API client for Hummingbird to manage anime and manga watch lists
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren
 * @copyright   Copyright (c) 2015 - 2016
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 * @license     MIT
 */

namespace Aviat\AnimeClient\Hummingbird\Enum;

use Aviat\Ion\Enum as BaseEnum;

/**
 * Possible values for watching status for the current anime
 */
class AnimeWatchingStatus extends BaseEnum {
	const WATCHING = 'currently-watching';
	const PLAN_TO_WATCH = 'plan-to-watch';
	const COMPLETED = 'completed';
	const ON_HOLD = 'on-hold';
	const DROPPED = 'dropped';
}
// End of AnimeWatchingStatus.php