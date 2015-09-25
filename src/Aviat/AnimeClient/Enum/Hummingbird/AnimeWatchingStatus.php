<?php

namespace Aviat\AnimeClient\Enum\Hummingbird;

use Aviat\Ion\Enum;

class AnimeWatchingStatus extends Enum {
	const WATCHING = 'currently-watching';
	const PLAN_TO_WATCH = 'plan-to-watch';
	const COMPLETED = 'completed';
	const ON_HOLD = 'on-hold';
	const DROPPED = 'dropped';
}
// End of AnimeWatchingStatus.php