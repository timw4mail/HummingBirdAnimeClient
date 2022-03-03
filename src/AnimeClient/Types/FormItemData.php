<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2021  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Types;

/**
 * Type representing a Media object for editing/syncing
 */
class FormItemData extends AbstractType {
	public ?string $notes = null;

	public ?bool $private = FALSE;

	public ?int $progress = NULL;

	public ?int $rating = null;

	public ?int $ratingTwenty = NULL;

	public string|int $reconsumeCount;

	public bool $reconsuming = FALSE;

	public string $status;

	/**
	 * W3C Format Date string
	 */
	public ?string $updatedAt = null;
}
