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
 * Type representing the manga represented by the list item
 */
final class MangaListItemDetail extends AbstractType
{
	public array $genres = [];
	public string $id;
	public string $image;
	public string $slug;
	public string $title;
	public array $titles;
	public ?string $type;
	public string $url;
}
