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
 * @version     5.1
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Types;

/**
 * Type representing an anime watch list item
 */
final class AnimeListItem extends AbstractType {
	public ?string $id;

	public ?string $mal_id;

	/**
	 * @var string
	 */
	public $anilist_item_id;

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

	/**
	 * @var string|int
	 */
	public $user_rating = '';

	/**
	 * One of Aviat\AnimeClient\API\Enum\AnimeWatchingStatus
	 */
	public string $watching_status;

	public function setAnime($anime): void
	{
		$this->anime = Anime::from($anime);
	}
}
