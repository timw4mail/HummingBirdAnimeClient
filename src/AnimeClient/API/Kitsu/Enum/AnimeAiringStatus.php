<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4+
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2021  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu\Enum;

use Aviat\Ion\Enum as BaseEnum;

/**
 * Status of when anime is being/was/will be aired
 */
final class AnimeAiringStatus extends BaseEnum {
	public const NOT_YET_AIRED = 'Not Yet Aired';
	public const AIRING = 'Currently Airing';
	public const FINISHED_AIRING = 'Finished Airing';
}
// End of AnimeAiringStatus.php
