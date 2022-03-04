<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @copyright   2015 - 2022  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshome.page/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Enum;

use Aviat\Ion\Enum as BaseEnum;

/**
 * Types of actions when syncing lists from different APIs
 */
final class SyncAction extends BaseEnum
{
	public const CREATE = 'create';
	public const UPDATE = 'update';
	public const DELETE = 'delete';
}
