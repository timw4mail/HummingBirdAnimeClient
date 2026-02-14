<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.4
 *
 * @copyright   2015 - 2026  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.3
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu;

use Amp\Http\Client\Request;
use Aviat\AnimeClient\Types\FormItem;

/**
 * Kitsu API calls that mutate data, C/U/D parts of CRUD
 */
trait MutationTrait
{
	// -------------------------------------------------------------------------
	// ! Generic API calls
	// -------------------------------------------------------------------------
	/**
	 * Create a list item
	 * @param array<string, mixed> $data
	 */
	public function createListItem(array $data): null|Request
	{
		$data['user_id'] = $this->getUserId();
		if (($data['id'] ?? null) === null)
		{
			return null;
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

	/**
	 * Remove a list item
	 */
	public function deleteItem(FormItem $data): Request
	{
		return $this->listItem->delete($data['id']);
	}
}
