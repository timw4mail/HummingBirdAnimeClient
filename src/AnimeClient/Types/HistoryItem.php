<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.1
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Types;

use DateTimeImmutable;

class HistoryItem extends AbstractType {
	/**
	 * Title of the anime/manga
	 */
	public string $title = '';

	/**
	 * The url of the cover image
	 */
	public string $coverImg = '';

	/**
	 * The type of action done
	 */
	public string $action = '';

	/**
	 * Is this item a combination of items?
	 */
	public bool $isAggregate = FALSE;

	/**
	 * The kind of history event
	 */
	public string $kind = '';

	/**
	 * When the item was last updated
	 */
	public ?DateTimeImmutable $updated = NULL;

	/**
	 * Range of updated times for the aggregated item
	 */
	public array $dateRange = [];

	/**
	 * Url to details page
	 */
	public string $url = '';

	/**
	 * The item before transformation
	 */
	public array $original = [];
}