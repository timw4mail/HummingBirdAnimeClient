<?php declare(strict_types=1);
/**
 * Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     AnimeListClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2017  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu\Enum;

use Aviat\Ion\Enum as BaseEnum;

/**
 * Possible values for current reading status of manga
 */
class MangaReadingStatus extends BaseEnum {
	const READING = 'current';
	const PLAN_TO_READ = 'planned';
	const DROPPED = 'dropped';
	const ON_HOLD = 'on_hold';
	const COMPLETED = 'completed';
}
// End of MangaReadingStatus.php