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

namespace Aviat\AnimeClient\API\Anilist;

use Amp\Artax\Request;

use Aviat\AnimeClient\API\ListItemInterface;
use Aviat\AnimeClient\API\Mapping\AnimeWatchingStatus;
use Aviat\AnimeClient\Types\FormItemData;

/**
 * CRUD operations for MAL list items
 */
final class ListItem implements ListItemInterface{
	use AnilistTrait;

	/**
	 * Create a list item
	 *
	 * @param array $data
	 * @return Request
	 */
	public function create(array $data): Request
	{
		return $this->mutateRequest('CreateMediaListEntry', $data);
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

	/**
	 * Get the data for a list item
	 *
	 * @param string $id
	 * @return array
	 */
	public function get(string $id): array
	{
		return $this->runQuery('MediaListItem', ['id' => $id]);
	}

	/**
	 * Increase the progress on the medium by 1
	 *
	 * @param string $id
	 * @param FormItemData $data
	 * @return Request
	 */
	public function increment(string $id, FormItemData $data): Request
	{
		return $this->mutateRequest('IncrementMediaListEntry', [
			'id' => $id,
			'progress' => $data['progress'],
		]);
	}

	/**
	 * Update a list item
	 *
	 * @param string $id
	 * @param FormItemData $data
	 * @return Request
	 */
	public function update(string $id, FormItemData $data): Request
	{
		// @TODO Handle weirdness with reWatching
		return $this->mutateRequest('UpdateMediaListEntry', [
			'id' => $id,
			'status' => AnimeWatchingStatus::KITSU_TO_ANILIST[$data['status']],
			'score' => $data['rating'] * 20,
			'progress' => $data['progress'],
			'repeat' => (int)$data['reconsumeCount'],
			'private' => (bool)$data['private'],
			'notes' => $data['notes'],
		]);
	}
}