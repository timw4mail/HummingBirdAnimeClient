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
 * Type representing an Anime object for display
 */
final class MangaListItem extends AbstractType {

	public string $id;

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

	public bool $rereading = false;

	public ?int $reread;

	public string|int|null $user_rating;
}

