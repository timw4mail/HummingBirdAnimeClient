<?php

namespace Aviat\AnimeClient\Hummingbird\Enum;

use Aviat\Ion\Enum as BaseEnum;

/**
 * Status of when anime is being/was/will be aired
 */
class AnimeAiringStatus extends BaseEnum {
	const NOT_YET_AIRED = 'Not Yet Aired';
	const AIRING = 'Currently Airing';
	const FINISHED_AIRING = 'Finished Airing';
}
// End of AnimeAiringStatus.php