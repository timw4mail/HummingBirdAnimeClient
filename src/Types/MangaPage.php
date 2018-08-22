<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2018  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Types;

/**
 * Type representing an Anime object for display
 */
final class MangaPage extends AbstractType {
	public $chapter_count;
	public $cover_image;
	public $genres;
	public $id;
	public $included;
	public $manga_type;
	public $synopsis;
	public $title;
	public $titles;
	public $url;
	public $volume_count;
}
