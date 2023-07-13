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

namespace Aviat\AnimeClient\API\Anilist\Types;

use Aviat\AnimeClient\Types\AbstractType;

class MediaListEntry extends AbstractType
{
	public int|string $id;
	public ?string $notes;
	public ?bool $private;
	public int $progress;
	public ?int $repeat;
	public string $status;
	public ?int $score;
}
