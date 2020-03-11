<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.3
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
 * Type representing an Anime object for display
 */
final class MangaListItem extends AbstractType {
	/**
	 * @var string
	 */
	public $id;

	/**
	 * @var string
	 */
	public $mal_id;

	/**
	 * @var array
	 */
	public $chapters = [
		'read' => 0,
		'total' => 0,
	];

	/**
	 * @var array
	 */
	public $volumes = [
		'read' => '-',
		'total' => 0,
	];

	/**
	 * @var object
	 */
	public $manga;

	/**
	 * @var string
	 */
	public $reading_status;

	/**
	 * @var string
	 */
	public $notes;

	/**
	 * @var bool
	 */
	public $rereading;

	/**
	 * @var int
	 */
	public $reread;

	/**
	 * @var int
	 */
	public $user_rating;
}

