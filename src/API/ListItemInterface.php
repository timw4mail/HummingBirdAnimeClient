<?php declare(strict_types=1);
/**
 * Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     AnimeListClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2016  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API;

/**
 * Common interface for anime and manga list item CRUD
 */
interface ListItemInterface {
	
	/**
	 * Create a list item
	 *
	 * @param array $data - 
	 * @return bool
	 */
	public function create(array $data): bool;
	
	/**
	 * Retrieve a list item
	 *
	 * @param string $id - The id of the list item
	 * @return array 
	 */
	public function get(string $id): array;
	
	/**
	 * Update a list item
	 * 
	 * @param string $id - The id of the list item to update
	 * @param array $data - The data with which to update the list item
	 * @return bool
	 */
	public function update(string $id, array $data): bool;
	
	/**
	 * Delete a list item
	 *
	 * @param string $id - The id of the list item to delete
	 * @return bool
	 */
	public function delete(string $id): bool;
}