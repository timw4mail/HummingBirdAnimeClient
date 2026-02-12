<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.4
 *
 * @copyright   2015 - 2026  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.3
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Types;

/**
 * Type representing a Kitsu user for display
 */
final class User extends AbstractType
{
	public null|string $about;

	public null|string $avatar;

	public null|string $birthday;

	public string $joinDate;

	public null|string $gender;

	/**
	 * @var array<mixed>|null
	 */
	public null|array $favorites;

	public null|string $location;

	public null|string $name;

	public null|string $slug;

	/**
	 * @var array<mixed>|null
	 */
	public null|array $stats;

	/**
	 * @var array<mixed>
	 */
	public array $waifu;

	public null|string $website;
}
