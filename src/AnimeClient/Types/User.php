<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.1
 *
 * @copyright   2015 - 2023  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Types;

/**
 * Type representing a Kitsu user for display
 */
final class User extends AbstractType
{
	public ?string $about;
	public ?string $avatar;
	public ?string $birthday;
	public string $joinDate;
	public ?string $gender;
	public ?array $favorites;
	public ?string $location;
	public ?string $name;
	public ?string $slug;
	public ?array $stats;
	public array $waifu;
	public ?string $website;
}
