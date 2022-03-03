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

use Aviat\AnimeClient\API\Kitsu\Enum\AnimeAiringStatus;

/**
 * Type representing an anime within a watch list
 */
class Anime extends AbstractType {
	public ?string $age_rating = null;

	public ?string $age_rating_guide = null;

	public ?string $cover_image = null;

	public ?int $episode_count = null;

	public ?int $episode_length = null;

	public array $genres = [];

	public string $id = '';

	public ?string $show_type = null;

	public ?string $slug = null;

	public string $status = AnimeAiringStatus::FINISHED_AIRING;

	public ?array $streaming_links = [];

	public ?string $synopsis = null;

	public ?string $title = null;

	public array $titles = [];

	public array $titles_more = [];

	public ?string $trailer_id = null;

	/**
	 * Length of the entire series in seconds
	 */
	public ?int $total_length = null;

	/**
	 * Kitsu detail page url
	 */
	public ?string $url = null;
}