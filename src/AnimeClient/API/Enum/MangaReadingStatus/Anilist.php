<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.4
 *
 * @copyright   2015 - 2026  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.3
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Enum\MangaReadingStatus;

use Aviat\Ion\ConstList;

/**
 * Possible values for watching status for the current anime
 */
final class Anilist extends ConstList
{
	public const READING = 'CURRENT';
	public const COMPLETED = 'COMPLETED';
	public const ON_HOLD = 'PAUSED';
	public const DROPPED = 'DROPPED';
	public const PLAN_TO_READ = 'PLANNING';
	public const REPEATING = 'REPEATING';
}
