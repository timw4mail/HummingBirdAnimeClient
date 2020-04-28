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
 * @version     5
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Types;

/**
 * Type representing a character for display
 */
final class Character extends AbstractType {
	/**
	 * @var array
	 */
	public array $castings = [];

	/**
	 * @var string
	 */
	public ?string $description;

	/**
	 * @var string
	 */
	public $id;

	/**
	 * @var array
	 */
	public array $included = [];

	/**
	 * @var Media
	 */
	public ?Media $media;

	/**
	 * @var string
	 */
	public ?string $name;

	/**
	 * @var array
	 */
	public array $names = [];

	/**
	 * @var array
	 */
	public array $otherNames = [];

	public function setMedia ($media): void
	{
		$this->media = Media::from($media);
	}
}