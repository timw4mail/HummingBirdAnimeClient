<?php

namespace Aviat\AnimeClient\Enum\Hummingbird;

use Aviat\Ion\Enum;

class MangaReadingStatus extends Enum {
	const READING = 'Currently Reading';
	const PLAN_TO_READ = 'Plan to Read';
	const DROPPED = 'Dropped';
	const ON_HOLD = 'On Hold';
	const COMPLETED = 'Completed';
}
// End of MangaReadingStatus.php