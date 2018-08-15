<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2018  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Anilist;

use Amp\Artax\{FormBody, Request};
use Aviat\AnimeClient\Types\AbstractType;
use Aviat\Ion\Di\ContainerAware;

/**
 * CRUD operations for MAL list items
 */
final class ListItem {
	use AnilistTrait;
	use ContainerAware;

	/**
	 * Create a list item
	 *
	 * @param array $data
	 * @param string $type
	 * @return Request
	 */
	public function create(array $data, string $type = 'anime'): Request
	{
		// @TODO: implement
	}

	/**
	 * Delete a list item
	 *
	 * @param string $id
	 * @param string $type
	 * @return Request
	 */
	public function delete(string $id, string $type = 'anime'): Request
	{
		// @TODO: implement
	}

	public function get(string $id): array
	{
		// @TODO: implement
	}

	/**
	 * Update a list item
	 *
	 * @param string $id
	 * @param AbstractType $data
	 * @param string $type
	 * @return Request
	 */
	public function update(string $id, AbstractType $data, string $type = 'anime'): Request
	{
		// @TODO: implement
	}
}