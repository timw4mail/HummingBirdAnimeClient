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

use Aviat\AnimeClient\API\Kitsu\Enum\AnimeAiringStatus;

/**
 * Type representing an anime within a watch list
 */
class Anime extends AbstractType {
	/**
	 * @var string
	 */
	public ?string $age_rating;

	/**
	 * @var string
	 */
	public ?string $age_rating_guide;

	/**
	 * @var string
	 */
	public ?string $cover_image;

	/**
	 * @var string|int
	 */
	public ?int $episode_count;

	/**
	 * @var string|int
	 */
	public ?int $episode_length;

	/**
	 * @var array
	 */
	public array $genres = [];

	/**
	 * @var string|int
	 */
	public $id = '';

	/**
	 * @var array
	 */
	public array $included = [];

	/**
	 * @var string
	 */
	public ?string $show_type;

	/**
	 * @var string
	 */
	public ?string $slug;

	/**
	 * @var AnimeAiringStatus
	 */
	public string $status = AnimeAiringStatus::FINISHED_AIRING;

	/**
	 * @var array
	 */
	public ?array $streaming_links = [];

	/**
	 * @var string
	 */
	public ?string $synopsis;

	/**
	 * @var string
	 */
	public ?string $title;

	/**
	 * @var array
	 */
	public array $titles = [];

	/**
	 * @var array
	 */
	public array $titles_more = [];

	/**
	 * @var string
	 */
	public ?string $trailer_id;

	/**
	 * @var string
	 */
	public ?string $url;
}