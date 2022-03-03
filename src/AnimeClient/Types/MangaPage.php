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
	public ?string $age_rating;

	public ?string $age_rating_guide;

	public array $characters;

	public ?int $chapter_count;

	public ?string $cover_image;

	public array $genres;

	public array $links;

	public string $id;

	public string $manga_type;

	public string $status = MangaPublishingStatus::FINISHED;

	public array $staff;

	public string $synopsis;

	public string $title;

	public array $titles;

	/**
	 * A potentially longer list of titles for the details page
	 */
	public array $titles_more;

	public string $url;

	public ?int $volume_count;
}
