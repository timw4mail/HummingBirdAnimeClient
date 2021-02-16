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

use Aviat\AnimeClient\API\Kitsu\Enum\MangaPublishingStatus;

/**
 * Type representing an Anime object for display
 */
final class MangaPage extends AbstractType {
	/**
	 * @var string|null
	 */
	public ?string $age_rating;

	/**
	 * @var string|null
	 */
	public ?string $age_rating_guide;

	/**
	 * @var array
	 */
	public array $characters;

	/**
	 * @var int|null
	 */
	public ?int $chapter_count;

	/**
	 * @var string|null
	 */
	public ?string  $cover_image;

	/**
	 * @var array
	 */
	public array $genres;

	/**
	 * @var array
	 */
	public array $links;

	/**
	 * @var string
	 */
	public string $id;

	/**
	 * @var string
	 */
	public string $manga_type;

	/**
	 * @var string
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
	 * A potentially longer list of titles for the details page
	 */
	public array $titles_more;

	/**
	 * @var string
	 */
	public string $url;

	/**
	 * @var int|null
	 */
	public ?int $volume_count;
}
