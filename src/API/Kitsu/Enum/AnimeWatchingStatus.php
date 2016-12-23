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
 * @copyright   2015 - 2016  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu\Enum;

use Aviat\Ion\Enum as BaseEnum;

/**
 * Possible values for watching status for the current anime
 */
class AnimeWatchingStatus extends BaseEnum {
	const WATCHING = 1;
	const PLAN_TO_WATCH = 2;
	const COMPLETED = 3;
	const ON_HOLD = 4;
	const DROPPED = 5;
}
// End of AnimeWatchingStatus.php