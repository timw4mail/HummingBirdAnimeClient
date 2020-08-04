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

namespace Aviat\AnimeClient\API;

use Amp\Http\Client\Request;
use Aviat\AnimeClient\Types\FormItemData;

/**
 * Common interface for anime and manga list item CRUD
 */
abstract class AbstractListItem {

	/**
	 * Create a list item
	 *
	 * @param array $data -
	 * @return Request
	 */
	abstract public function create(array $data): Request;

	/**
	 * Retrieve a list item
	 *
	 * @param string $id - The id of the list item
	 * @return array
	 */
	abstract public function get(string $id): array;

	/**
	 * Increase progress on a list item
	 *
	 * @param string $id
	 * @param FormItemData $data
	 * @return Request
	 */
	abstract public function increment(string $id, FormItemData $data): Request;

	/**
	 * Update a list item
	 *
	 * @param string $id - The id of the list item to update
	 * @param FormItemData $data - The data with which to update the list item
	 * @return Request
	 */
	abstract public function update(string $id, FormItemData $data): Request;

	/**
	 * Delete a list item
	 *
	 * @param string $id - The id of the list item to delete
	 * @return Request
	 */
	abstract public function delete(string $id):?Request;
}