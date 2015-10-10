<?php

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