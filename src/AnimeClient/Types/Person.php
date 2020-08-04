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
 * @version     5.1
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Types;

/**
 * Type representing a person for display
 */
final class Person extends AbstractType {
	/**
	 * @var string
	 */
	public $id;

	/**
	 * @var string
	 */
	public ?string $name;

	/**
	 * @var Characters
	 */
	public ?Characters $characters;

	/**
	 * @var array
	 */
	public array $staff = [];

	public function setCharacters($characters): void
	{
		$this->characters = Characters::from($characters);
	}
}