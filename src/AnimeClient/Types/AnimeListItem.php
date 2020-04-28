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

/**
 * Type representing an anime watch list item
 */
final class AnimeListItem extends AbstractType {
	/**
	 * @var string
	 */
	public ?string $id;

	/**
	 * @var string
	 */
	public ?string $mal_id;

	/**
	 * @var string
	 */
	public $anilist_item_id;

	/**
	 * @var array
	 */
	public array $episodes = [
		'length' => 0,
		'total' => 0,
		'watched' => '',
	];

	/**
	 * @var array
	 */
	public array $airing = [
		'status' => '',
		'started' => '',
		'ended' => '',
	];

	/**
	 * @var Anime
	 */
	public ?Anime $anime;

	/**
	 * @var string
	 */
	public ?string $notes;

	/**
	 * @var bool
	 */
	public bool $private = FALSE;

	/**
	 * @var bool
	 */
	public bool $rewatching = FALSE;

	/**
	 * @var int
	 */
	public int $rewatched = 0;

	/**
	 * @var string|int
	 */
	public $user_rating = '';

	/**
	 * One of Aviat\AnimeClient\API\Enum\AnimeWatchingStatus
	 *
	 * @var string
	 */
	public $watching_status;

	public function setAnime($anime): void
	{
		$this->anime = Anime::from($anime);
	}
}
