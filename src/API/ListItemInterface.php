<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
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

namespace Aviat\AnimeClient\API;

use Amp\Artax\Request;
use Aviat\AnimeClient\Types\FormItemData;

/**
 * Common interface for anime and manga list item CRUD
 */
interface ListItemInterface {

	/**
	 * Create a list item
	 *
	 * @param array $data -
	 * @return Request
	 */
	public function create(array $data): Request;

	/**
	 * Retrieve a list item
	 *
	 * @param string $id - The id of the list item
	 * @return array
	 */
	public function get(string $id): array;

	/**
	 * Increase progress on a list item
	 *
	 * @param string $id
	 * @param FormItemData $data
	 * @return Request
	 */
	public function increment(string $id, FormItemData $data): Request;

	/**
	 * Update a list item
	 *
	 * @param string $id - The id of the list item to update
	 * @param FormItemData $data - The data with which to update the list item
	 * @return Request
	 */
	public function update(string $id, FormItemData $data): Request;

	/**
	 * Delete a list item
	 *
	 * @param string $id - The id of the list item to delete
	 * @return Request
	 */
	public function delete(string $id): Request;
}