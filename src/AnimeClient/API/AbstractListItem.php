<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @copyright   2015 - 2022  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshome.page/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API;

use Amp\Http\Client\Request;
use Aviat\AnimeClient\Types\FormItemData;

/**
 * Common interface for anime and manga list item CRUD
 */
abstract class AbstractListItem
{
	/**
	 * Create a list item
	 *
	 * @param array $data -
	 */
	abstract public function create(array $data): Request;

	/**
	 * Create a full list item for syncing
	 */
	abstract public function createFull(array $data): Request;

	/**
	 * Retrieve a list item
	 *
	 * @param string $id - The id of the list item
	 */
	abstract public function get(string $id): array;

	/**
	 * Increase progress on a list item
	 */
	abstract public function increment(string $id, FormItemData $data): Request;

	/**
	 * Update a list item
	 *
	 * @param string $id - The id of the list item to update
	 * @param FormItemData $data - The data with which to update the list item
	 */
	abstract public function update(string $id, FormItemData $data): Request;

	/**
	 * Delete a list item
	 *
	 * @param string $id - The id of the list item to delete
	 */
	abstract public function delete(string $id): ?Request;
}
