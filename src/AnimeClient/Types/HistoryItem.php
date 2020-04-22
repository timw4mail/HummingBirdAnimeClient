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
 * @version     5
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Types;

class HistoryItem extends AbstractType {
	/**
	 * @var string Title of the anime/manga
	 */
	public string $title = '';

	/**
	 * @var string The url of the cover image
	 */
	public string $coverImg = '';

	/**
	 * @var string The type of action done
	 */
	public string $action = '';

	/**
	 * @var bool Is this item a combination of items?
	 */
	public bool $isAggregate = FALSE;

	/**
	 * @var string The kind of history event
	 */
	public string $kind = '';

	/**
	 * @var string When the item was last updated
	 */
	public string $updated = '';

	public $original;
}