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

namespace Aviat\AnimeClient\Types;

/**
 * Type representing a Kitsu user for display
 */
final class User extends AbstractType {
	public ?string $about = null;

	public ?string $avatar = null;

	public ?array $favorites = null;

	public ?string $location = null;

	public ?string $name = null;

	public ?string $slug = null;

	public ?array $stats = null;

	public ?array $waifu = null;

	public ?string $website = null;
}