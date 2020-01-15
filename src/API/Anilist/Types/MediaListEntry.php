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

namespace Aviat\AnimeClient\API\Anilist\Types;

use Aviat\AnimeClient\Types\AbstractType;

class MediaListEntry extends AbstractType {
	/**
	 * @var int
	 */
	public $id;

	/**
	 * @var string
	 */
	public $notes;

	/**
	 * @var bool
	 */
	public $private;

	/**
	 * @var int
	 */
	public $progress;

	/**
	 * @var int
	 */
	public $repeat;

	/**
	 * @var string
	 */
	public $status;

	/**
	 * @var int
	 */
	public $score;
}