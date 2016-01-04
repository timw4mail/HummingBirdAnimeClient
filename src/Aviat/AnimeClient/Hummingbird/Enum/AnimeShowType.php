<?php
/**
 * Hummingbird Anime Client
 *
 * An API client for Hummingbird to manage anime and manga watch lists
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren
 * @copyright   Copyright (c) 2015 - 2016
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 * @license     MIT
 */

namespace Aviat\AnimeClient\Hummingbird\Enum;

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