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

namespace Aviat\Ion\Type;

use Aviat\Ion\EnumTrait;

enum EventType: string
{
	use EnumTrait;

	case CLEAR_CACHE = '::clear-cache::';

	case RESET_CACHE_KEY = '::reset-cache-key::';

	case TEST = '::test::';

	case UNAUTHORIZED = '::unauthorized::';
}
