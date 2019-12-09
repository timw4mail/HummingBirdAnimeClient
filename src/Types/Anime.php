<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.2
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2019  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Types;

use Aviat\AnimeClient\API\Kitsu\Enum\AnimeAiringStatus;

/**
 * Type representing an anime within a watch list
 */
class Anime extends AbstractType {
	/**
	 * @var string
	 */
	public $age_rating;

	/**
	 * @var string
	 */
	public $age_rating_guide;

	/**
	 * @var string
	 */
	public $cover_image;

	/**
	 * @var string|int
	 */
	public $episode_count;

	/**
	 * @var string|int
	 */
	public $episode_length;

	/**
	 * @var array
	 */
	public $genres;

	/**
	 * @var string
	 */
	public $id;

	/**
	 * @var array
	 */
	public $included;

	/**
	 * @var string
	 */
	public $show_type;

	/**
	 * @var string
	 */
	public $slug;

	/**
	 * @var AnimeAiringStatus::NOT_YET_AIRED | AnimeAiringStatus::AIRING | AnimeAiringStatus::FINISHED_AIRING
	 */
	public $status;

	/**
	 * @var array
	 */
	public $streaming_links;

	/**
	 * @var string
	 */
	public $synopsis;

	/**
	 * @var string
	 */
	public $title;

	/**
	 * @var array
	 */
	public $titles;

	/**
	 * @var string
	 */
	public $trailer_id;

	/**
	 * @var string
	 */
	public $url;
}