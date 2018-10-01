<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.1
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2018  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.1
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Anilist;

use Amp\Artax\Request;

use Aviat\AnimeClient\API\ListItemInterface;
use Aviat\AnimeClient\API\Enum\AnimeWatchingStatus\Anilist as AnilistStatus;
use Aviat\AnimeClient\API\Mapping\AnimeWatchingStatus;
use Aviat\AnimeClient\Types\FormItemData;

/**
 * CRUD operations for MAL list items
 */
final class ListItem implements ListItemInterface{
	use AnilistTrait;

	/**
	 * Create a minimal list item
	 *
	 * @param array $data
	 * @return Request
	 */
	public function create(array $data): Request
	{
		return $this->mutateRequest('CreateMediaListEntry', $data);
	}

	/**
	 * Create a fleshed-out list item
	 *
	 * @param array $data
	 * @return Request
	 */
	public function createFull(array $data): Request
	{
		return $this->mutateRequest('CreateFullMediaListEntry', $data);
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
		return $this->mutateRequest('DeleteMediaListEntry', ['id' => $id]);
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
		$array = $data->toArray();

		$notes = $data['notes'] ?? '';
		$progress = array_key_exists('progress', $array) ? $data['progress'] : 0;
		$private = array_key_exists('private', $array) ? (bool)$data['private'] : false;
		$rating = array_key_exists('ratingTwenty', $array) ? $data['ratingTwenty'] : NULL;
		$status = ($data['reconsuming'] === true) ? AnilistStatus::REPEATING : AnimeWatchingStatus::KITSU_TO_ANILIST[$data['status']];

		$updateData = [
			'id' => (int)$id,
			'status' => $status,
			'score' => $rating * 5,
			'progress' => $progress,
			'repeat' => (int)$data['reconsumeCount'],
			'private' => $private,
			'notes' => $notes,
		];

		return $this->mutateRequest('UpdateMediaListEntry', $updateData);
	}
}