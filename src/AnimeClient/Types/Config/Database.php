<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2021  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Types\Config;

use Aviat\AnimeClient\Types\AbstractType;

class Database extends AbstractType {

	public string $type = 'sqlite';

	public ?string $host;

	public ?string $user;

	public ?string $pass;

	public string|int|null $port;

	public ?string $database;

	public ?string $file;
}