<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4+
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu;

use Amp\Http\Client\Request;
use Aviat\AnimeClient\Types\FormItem;
use Aviat\Banker\Exception\InvalidArgumentException;

/**
 * Kitsu API calls that mutate data, C/U/D parts of CRUD
 */
trait MutationTrait {
	// -------------------------------------------------------------------------
	// ! Generic API calls
	// -------------------------------------------------------------------------

	/**
	 * Create a list item
	 *
	 * @param array $data
	 * @return Request
	 * @throws InvalidArgumentException
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
	 *
	 * @param FormItem $data
	 * @return Request
	 */
	public function incrementListItem(FormItem $data): Request
	{
		return $this->listItem->increment($data['id'], $data['data']);
	}

	/**
	 * Modify a list item
	 *
	 * @param FormItem $data
	 * @return Request
	 */
	public function updateListItem(FormItem $data): Request
	{
		return $this->listItem->update($data['id'], $data['data']);
	}

	/**
	 * Remove a list item
	 *
	 * @param string $id - The id of the list item to remove
	 * @return Request
	 */
	public function deleteListItem(string $id): Request
	{
		return $this->listItem->delete($id);
	}
}