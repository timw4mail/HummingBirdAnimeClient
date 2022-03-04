<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @copyright   2015 - 2022  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshome.page/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Types;

/**
 * Type representing an anime watch list item
 */
final class AnimeListItem extends AbstractType
{
	public ?string $id;
	public ?string $anilist_id;
	public ?string $mal_id;
	public array $episodes = [
		'length' => 0,
		'total' => 0,
		'watched' => '',
	];
	public array $airing = [
		'status' => '',
		'started' => '',
		'ended' => '',
	];
	public ?Anime $anime;
	public ?string $notes;
	public bool $private = FALSE;
	public bool $rewatching = FALSE;
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
