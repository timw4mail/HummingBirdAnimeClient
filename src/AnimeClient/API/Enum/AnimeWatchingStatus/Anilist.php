<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2021  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Enum\AnimeWatchingStatus;

use Aviat\Ion\Enum;

/**
 * Possible values for watching status for the current anime
 */
final class Anilist extends Enum {
	public const WATCHING = 'CURRENT';
	public const COMPLETED = 'COMPLETED';
	public const ON_HOLD = 'PAUSED';
	public const DROPPED = 'DROPPED';
	public const PLAN_TO_WATCH = 'PLANNING';
	public const REPEATING = 'REPEATING';
}