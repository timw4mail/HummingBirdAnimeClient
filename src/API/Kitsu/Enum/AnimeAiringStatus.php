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
 * Status of when anime is being/was/will be aired
 */
class AnimeAiringStatus extends BaseEnum {
	const NOT_YET_AIRED = 'Not Yet Aired';
	const AIRING = 'Currently Airing';
	const FINISHED_AIRING = 'Finished Airing';
}
// End of AnimeAiringStatus.php
