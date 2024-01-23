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

namespace Aviat\AnimeClient\API\Kitsu\Enum;

use Aviat\Ion\Enum as BaseEnum;

/**
 * Status of when anime is being/was/will be aired
 */
final class MediaStatus extends BaseEnum
{
	public const CURRENT = 'CURRENT';
	public const PLANNED = 'PLANNED';
	public const ON_HOLD = 'ON_HOLD';
	public const DROPPED = 'DROPPED';
	public const COMPLETED = 'COMPLETED';
}
// End of MediaStatus
