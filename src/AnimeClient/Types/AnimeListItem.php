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
 * Type representing an anime watch list item
 */
final class AnimeListItem extends AbstractType
{
	public null|string $id;

	public null|string $anilist_id;

	public null|string $mal_id;

	/**
	 * @var array<string, int|string>
	 */
	public array $episodes = [
		'length' => 0,
		'total' => 0,
		'watched' => '',
	];

	/**
	 * @var array<string, int|string>
	 */
	public array $airing = [
		'status' => '',
		'started' => '',
		'ended' => '',
	];

	public null|Anime $anime;

	public null|string $notes;

	public bool $private = false;

	public bool $rewatching = false;

	public int $rewatched = 0;

	public string|int $user_rating = '';

	/**
	 * One of Aviat\AnimeClient\API\Enum\AnimeWatchingStatus
	 */
	public string $watching_status;

	public function setAnime(mixed $anime): void
	{
		$this->anime = Anime::from($anime);
	}
}
