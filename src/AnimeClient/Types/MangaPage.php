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

use Aviat\AnimeClient\API\Kitsu\Enum\MangaPublishingStatus;

/**
 * Type representing an Anime object for display
 */
final class MangaPage extends AbstractType {
	/**
	 * @var string
	 */
	public ?string $age_rating;

	/**
	 * @var string
	 */
	public ?string $age_rating_guide;

	/**
	 * @var array
	 */
	public $characters;

	/**
	 * @var int
	 */
	public $chapter_count;

	/**
	 * @var string
	 */
	public $cover_image;

	/**
	 * @var array
	 */
	public $genres;

	/**
	 * @var string
	 */
	public $id;

	/**
	 * @var string
	 */
	public $manga_type;

	/**
	 * @var MangaPublishingStatus
	 */
	public string $status = MangaPublishingStatus::FINISHED;

	/**
	 * @var array
	 */
	public array $staff;

	/**
	 * @var string
	 */
	public string $synopsis;

	/**
	 * @var string
	 */
	public string $title;

	/**
	 * @var array
	 */
	public array $titles;

	/**
	 * @var string
	 */
	public string $url;

	/**
	 * @var int
	 */
	public ?int $volume_count;
}
