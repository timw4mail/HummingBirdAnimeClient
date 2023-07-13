<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.1
 *
 * @copyright   2015 - 2023  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Enum\MangaReadingStatus;

use Aviat\Ion\Enum;

/**
 * Possible values for current reading status of manga
 */
final class Route extends Enum
{
	public const ALL = 'all';
	public const READING = 'reading';
	public const PLAN_TO_READ = 'plan_to_read';
	public const DROPPED = 'dropped';
	public const ON_HOLD = 'on_hold';
	public const COMPLETED = 'completed';
}
