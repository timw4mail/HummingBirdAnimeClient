<?php

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