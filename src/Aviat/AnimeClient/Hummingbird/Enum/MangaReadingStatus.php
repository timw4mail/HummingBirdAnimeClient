<?php

namespace Aviat\AnimeClient\Hummingbird\Enum;

use Aviat\Ion\Enum as BaseEnum;

/**
 * Possible values for current reading status of manga
 */
class MangaReadingStatus extends BaseEnum {
	const READING = 'Currently Reading';
	const PLAN_TO_READ = 'Plan to Read';
	const DROPPED = 'Dropped';
	const ON_HOLD = 'On Hold';
	const COMPLETED = 'Completed';
}
// End of MangaReadingStatus.php