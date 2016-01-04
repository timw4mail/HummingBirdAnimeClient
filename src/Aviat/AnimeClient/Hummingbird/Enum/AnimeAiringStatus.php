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
 * Status of when anime is being/was/will be aired
 */
class AnimeAiringStatus extends BaseEnum {
	const NOT_YET_AIRED = 'Not Yet Aired';
	const AIRING = 'Currently Airing';
	const FINISHED_AIRING = 'Finished Airing';
}
// End of AnimeAiringStatus.php