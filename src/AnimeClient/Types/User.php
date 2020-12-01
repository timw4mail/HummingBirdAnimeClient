<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.1
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Types;

/**
 * Type representing a Kitsu user for display
 */
final class User extends AbstractType {
	/**
	 * @var string
	 */
	public ?string $about;

	/**
	 * @var string
	 */
	public ?string $avatar;

	/**
	 * @var array
	 */
	public ?array $favorites;

	/**
	 * @var string
	 */
	public ?string $location;

	/**
	 * @var string
	 */
	public ?string $name;

	/**
	 * @var string
	 */
	public ?string $slug;

	/**
	 * @var array
	 */
	public ?array $stats;

	/**
	 * @var array
	 */
	public ?array $waifu;

	/**
	 * @var string
	 */
	public ?string $website;
}