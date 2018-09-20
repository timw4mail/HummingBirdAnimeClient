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
final class AnimeListItem extends AbstractType {
	public $id;
	public $mal_id;
	public $anilist_item_id;
	public $episodes = [
		'length' => 0,
		'total' => 0,
		'watched' => '',
	];
	public $airing = [
		'status' => '',
		'started' => '',
		'ended' => '',
	];
	public $anime;
	public $notes = '';
	public $private;
	public $rewatching;
	public $rewatched;
	public $user_rating;
	public $watching_status;
}
