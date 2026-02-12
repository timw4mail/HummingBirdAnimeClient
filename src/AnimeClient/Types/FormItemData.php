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

namespace Aviat\AnimeClient\Types;

/**
 * Type representing a Media object for editing/syncing
 */
class FormItemData extends AbstractType
{
	public null|string $notes;

	public null|bool $private = false;

	public null|int $progress = null;

	public null|int $rating;

	public null|int $ratingTwenty = null;

	public string|int $reconsumeCount;

	public bool $reconsuming = false;

	public string $status;

	/**
	 * W3C Format Date string
	 */
	public null|string $updatedAt;
}
