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
 * Type of Anime
 */
class AnimeShowType extends BaseEnum {
	const TV = 'TV';
	const MOVIE = 'Movie';
	const OVA = 'OVA';
	const ONA = 'ONA';
	const SPECIAL = 'Special';
	const MUSIC = 'Music';
}
// End of AnimeShowType.php
