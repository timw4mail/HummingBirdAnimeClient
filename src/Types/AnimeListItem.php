<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.2
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.2
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
	public $id;

	/**
	 * @var string
	 */
	public $mal_id;

	/**
	 * @var string
	 */
	public $anilist_item_id;

	/**
	 * @var array
	 */
	public $episodes = [
		'length' => 0,
		'total' => 0,
		'watched' => '',
	];

	/**
	 * @var array
	 */
	public $airing = [
		'status' => '',
		'started' => '',
		'ended' => '',
	];

	/**
	 * @var Anime
	 */
	public $anime;

	/**
	 * @var string
	 */
	public $notes = '';

	/**
	 * @var bool
	 */
	public $private;

	/**
	 * @var bool
	 */
	public $rewatching;

	/**
	 * @var int
	 */
	public $rewatched;

	/**
	 * @var int
	 */
	public $user_rating;

	/**
	 * One of Aviat\AnimeClient\API\Enum\AnimeWatchingStatus
	 *
	 * @var string
	 */
	public $watching_status;

	public function setAnime($anime): void
	{
		$this->anime = new Anime($anime);
	}
}
