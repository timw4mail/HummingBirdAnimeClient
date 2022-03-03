<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2021  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu;

use Amp\Http\Client\Request;
use Aviat\AnimeClient\Types\FormItem;

/**
 * Kitsu API calls that mutate data, C/U/D parts of CRUD
 */
trait MutationTrait {
	// -------------------------------------------------------------------------
	// ! Generic API calls
	// -------------------------------------------------------------------------
	/**
	 * Create a list item
	 */
	public function createListItem(array $data): ?Request
	{
		$data['user_id'] = $this->getUserId();
		if ($data['id'] === NULL)
		{
			return NULL;
		}

		return $this->listItem->create($data);
	}

	/**
	 * Increase the progress count for a list item
	 */
	public function incrementListItem(FormItem $data): Request
	{
		return $this->listItem->increment($data['id'], $data['data']);
	}

	/**
	 * Modify a list item
	 */
	public function updateListItem(FormItem $data): Request
	{
		return $this->listItem->update($data['id'], $data['data']);
	}

	/**
	 * Remove a list item
	 *
	 * @param string $id - The id of the list item to remove
	 */
	public function deleteListItem(string $id): Request
	{
		return $this->listItem->delete($id);
	}
}