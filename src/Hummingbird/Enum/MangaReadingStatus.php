<?php declare(strict_types=1);
/**
 * Hummingbird Anime Client
 *
 * An API client for Hummingbird to manage anime and manga watch lists
 *
 * PHP version 7
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