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

namespace Aviat\AnimeClient\API\Anilist;

use Amp\Http\Client\Request;

use Aviat\AnimeClient\API\AbstractListItem;
use Aviat\AnimeClient\API\Enum\AnimeWatchingStatus\Anilist as AnilistStatus;
use Aviat\AnimeClient\API\Mapping\AnimeWatchingStatus;
use Aviat\AnimeClient\Types\FormItemData;

/**
 * CRUD operations for MAL list items
 */
final class ListItem extends AbstractListItem
{
	use RequestBuilderTrait;

	/**
	 * Create a minimal list item
	 */
	public function create(array $data): Request
	{
		$checkedData = Types\MediaListEntry::check($data);

		return $this->requestBuilder->mutateRequest('CreateMediaListEntry', $checkedData ?? []);
	}

	/**
	 * Create a fleshed-out list item
	 */
	public function createFull(array $data): Request
	{
		$checkedData = Types\MediaListEntry::check($data);

		return $this->requestBuilder->mutateRequest('CreateFullMediaListEntry', $checkedData ?? []);
	}

	/**
	 * Delete a list item
	 */
	public function delete(string $id, string $type = 'anime'): Request
	{
		return $this->requestBuilder->mutateRequest('DeleteMediaListEntry', ['id' => $id]);
	}

	/**
	 * Get the data for a list item
	 */
	public function get(string $id): array
	{
		return $this->requestBuilder->runQuery('MediaListItem', ['id' => $id]);
	}

	/**
	 * Increase the progress on the medium by 1
	 */
	public function increment(string $id, FormItemData $data): Request
	{
		$checkedData = Types\MediaListEntry::check([
			'id' => $id,
			'progress' => $data->progress,
		]);

		return $this->requestBuilder->mutateRequest('IncrementMediaListEntry', $checkedData ?? []);
	}

	/**
	 * Update a list item
	 */
	public function update(string $id, FormItemData $data): Request
	{
		$notes = $data->notes ?? '';
		$progress = (int) $data->progress;
		$private = (bool) $data->private;
		$rating = $data->ratingTwenty;
		$status = ($data->reconsuming === TRUE)
			? AnilistStatus::REPEATING
			: AnimeWatchingStatus::KITSU_TO_ANILIST[$data->status];

		$updateData = Types\MediaListEntry::check([
			'id' => (int) $id,
			'status' => $status,
			'score' => $rating * 5,
			'progress' => $progress,
			'repeat' => (int) $data['reconsumeCount'],
			'private' => $private,
			'notes' => $notes,
		]);

		return $this->requestBuilder->mutateRequest('UpdateMediaListEntry', $updateData ?? []);
	}
}
