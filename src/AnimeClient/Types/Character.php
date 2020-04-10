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
	public $castings;

	/**
	 * @var string
	 */
	public $description;

	/**
	 * @var string
	 */
	public $id;

	/**
	 * @var array
	 */
	public $included;

	/**
	 * @var Media
	 */
	public $media;

	/**
	 * @var string
	 */
	public $name;

	/**
	 * @var array
	 */
	public $names;

	/**
	 * @var array
	 */
	public $otherNames;

	public function setMedia ($media): void
	{
		$this->media = new Media($media);
	}
}