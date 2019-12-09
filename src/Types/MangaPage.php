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
 * @copyright   2015 - 2019  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Types;

/**
 * Type representing an Anime object for display
 */
final class MangaPage extends AbstractType {
	/**
	 * @var array
	 */
	public $characters;

	/**
	 * @var int
	 */
	public $chapter_count;

	/**
	 * @var string
	 */
	public $cover_image;

	/**
	 * @var array
	 */
	public $genres;

	/**
	 * @var string
	 */
	public $id;

	/**
	 * @var array
	 */
	public $included;

	/**
	 * @var string
	 */
	public $manga_type;

	/**
	 * @var array
	 */
	public $staff;

	/**
	 * @var string
	 */
	public $synopsis;

	/**
	 * @var string
	 */
	public $title;

	/**
	 * @var array
	 */
	public $titles;

	/**
	 * @var string
	 */
	public $url;

	/**
	 * @var int
	 */
	public $volume_count;
}
