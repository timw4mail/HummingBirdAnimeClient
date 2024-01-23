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
 * Type representing a character for display
 */
final class Character extends AbstractType
{
	public array $castings = [];
	public ?string $description;
	public string $id;
	public ?Media $media;
	public string $image;
	public ?string $name;
	public array $names = [];
	public array $otherNames = [];

	public function setMedia(mixed $media): void
	{
		$this->media = Media::from($media);
	}
}
