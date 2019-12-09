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
 * Type representing the manga represented by the list item
 */
final class MangaListItemDetail extends AbstractType {
	/**
	 * @var array
	 */
	public $genres;

	/**
	 * @var string
	 */
	public $id;

	/**
	 * @var string
	 */
	public $image;

	/**
	 * @var string
	 */
	public $slug;

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
	public $type;

	/**
	 * @var string
	 */
	public $url;
}
