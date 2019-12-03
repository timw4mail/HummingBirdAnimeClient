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
 * @copyright   2015 - 2018  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.1
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Types;

/**
 * Type representing an anime within a watch list
 */
class Anime extends AbstractType {
	public $age_rating;
	public $age_rating_guide;
	public $cover_image;
	public $episode_count;
	public $episode_length;
	public $genres;
	public $id;
	public $included;
	public $show_type;
	public $slug;
	public $status;
	public $streaming_links;
	public $synopsis;
	public $title;
	public $titles;
	public $trailer_id;
	public $url;
}