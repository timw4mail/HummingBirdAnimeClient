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

use Aviat\AnimeClient\API\Kitsu\Enum\AnimeAiringStatus;

/**
 * Type representing an anime within a watch list
 */
class Anime extends AbstractType
{
	public null|string $age_rating;

	public null|string $age_rating_guide;

	public null|string $cover_image;

	public null|int $episode_count;

	public null|int $episode_length;

	/**
	 * @var list<string>
	 */
	public array $genres = [];

	public string $id = '';

	public null|string $show_type;

	public null|string $slug;

	public string $status = AnimeAiringStatus::FINISHED_AIRING->value;

	/**
	 * @var array<string, mixed>|null
	 */
	public null|array $streaming_links = [];

	public null|string $synopsis;

	public null|string $title;

	/**
	 * @var list<string>
	 */
	public array $titles = [];

	/**
	 * @var list<string>
	 */
	public array $titles_more = [];

	public null|string $trailer_id;

	/**
	 * Length of the entire series in seconds
	 */
	public null|int $total_length;

	/**
	 * Kitsu detail page url
	 */
	public null|string $url;
}
