<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.1
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
 * Type representing an Anime object for display
 */
final class MangaListItem extends AbstractType {
	public $id;
	public $mal_id;
	public $chapters = [
		'read' => 0,
		'total' => 0,
	];
	public $volumes = [
		'read' => '-',
		'total' => 0,
	];
	public $manga;
	public $reading_status;
	public $notes;
	public $rereading;
	public $reread;
	public $user_rating;
}

