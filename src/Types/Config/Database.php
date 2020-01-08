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

namespace Aviat\AnimeClient\Types\Config;

use Aviat\AnimeClient\Types\AbstractType;

class Database extends AbstractType {
	/**
	 * @var string
	 */
	public $type;

	/**
	 * @var string
	 */
	public $host;

	/**
	 * @var string
	 */
	public $user;

	/**
	 * @var string
	 */
	public $pass;

	/**
	 * @var string|int
	 */
	public $port;

	/**
	 * @var string
	 */
	public $database;

	/**
	 * @var string
	 */
	public $file;
}