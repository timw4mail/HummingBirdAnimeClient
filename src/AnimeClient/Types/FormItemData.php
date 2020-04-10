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

/**
 * Type representing a Media object for editing/syncing
 */
class FormItemData extends AbstractType {
	/**
	 * @var string
	 */
	public $notes;

	/**
	 * @var bool
	 */
	public $private;

	/**
	 * @var int
	 */
	public $progress;

	/**
	 * @var int
	 */
	public $rating;

	/**
	 * @var int
	 */
	public $ratingTwenty;

	/**
	 * @var int
	 */
	public $reconsumeCount;

	/**
	 * @var bool
	 */
	public $reconsuming;

	/**
	 * @var string
	 */
	public $status;

	/**
	 * W3C Format Date string
	 *
	 * @var string
	 */
	public $updatedAt;
}
