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
 * Type representing an Anime object for display
 */
final class MangaListItem extends AbstractType
{
	public string $id;
	public ?string $anilist_id;
	public ?string $mal_id;
	public array $chapters = [
		'read' => 0,
		'total' => 0,
	];
	public array $volumes = [
		'read' => '-',
		'total' => 0,
	];
	public object $manga;
	public string $reading_status;
	public ?string $notes;
	public bool $rereading = FALSE;
	public ?int $reread;
	public string|int|NULL $user_rating;
}
