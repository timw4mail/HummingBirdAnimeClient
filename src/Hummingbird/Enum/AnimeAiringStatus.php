<?php
/**
 * Hummingbird Anime Client
 *
 * An API client for Hummingbird to manage anime and manga watch lists
 *
 * PHP version 5.6
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2016  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     3.1
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
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