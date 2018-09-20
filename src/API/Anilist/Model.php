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
use Aviat\AnimeClient\API\Mapping\{AnimeWatchingStatus, MangaReadingStatus};
use Aviat\AnimeClient\Types\FormItem;

/**
 * Anilist API Model
 */
final class Model
{
	use AnilistTrait;
	/**
	 * @var ListItem
	 */
	private $listItem;

	/**
	 * Constructor
	 *
	 * @param ListItem $listItem
	 */
	public function __construct(ListItem $listItem)
	{
		$this->listItem = $listItem;
	}

	public function getAnimeList(): array
	{
		return $this->runQuery('UserAnimeList', ['name' => 'timw4mail']);
	}

	public function getMangaList(): array
	{
		return $this->runQuery('UserMangaList', ['name' => 'timw4mail']);
	}

	// -------------------------------------------------------------------------
	// ! Generic API calls
	// -------------------------------------------------------------------------

	/**
	 * Create a list item
	 *
	 * @param array $data
	 * @param string $type
	 * @return Request
	 */
	public function createListItem(array $data, string $type = 'anime'): Request
	{
		$createData = [];

		$mediaId = $this->getMediaIdFromMalId($data['malId'], strtoupper($type));

		if ($type === 'anime') {
			$createData = [
				'id' => $mediaId,
				'status' => AnimeWatchingStatus::KITSU_TO_ANILIST[$data['status']],
			];
		} elseif ($type === 'manga') {
			$createData = [
				'id' => $mediaId,
				'status' => MangaReadingStatus::KITSU_TO_ANILIST[$data['status']],
			];
		}

		return $this->listItem->create($createData, $type);
	}

	/**
	 * Get the data for a specific list item, generally for editing
	 *
	 * @param string $malId - The unique identifier of that list item
	 * @return mixed
	 */
	public function getListItem(string $malId): array
	{
		$id = $this->getListIdFromMalId($malId);
		return $this->listItem->get($id)['data']['MediaList'];
	}

	/**
	 * Increase the watch count for the current list item
	 *
	 * @param FormItem $data
	 * @return Request
	 */
	public function incrementListItem(FormItem $data): Request
	{
		$id = $this->getListIdFromMalId($data['mal_id']);

		return $this->listItem->increment($id, $data['data']);
	}

	/**
	 * Modify a list item
	 *
	 * @param FormItem $data
	 * @return Request
	 */
	public function updateListItem(FormItem $data): Request
	{
		$id = $this->getListIdFromMalId($data['mal_id']);

		return $this->listItem->update($id, $data['data']);
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

	/**
	 * Get the id of the specific list entry from the malId
	 *
	 * @param string $malId
	 * @return string
	 */
	public function getListIdFromMalId(string $malId): string
	{
		$info = $this->runQuery('ListItemIdByMalId', ['id' => $malId]);
		return (string)$info['data']['Media']['mediaListEntry']['id'];
	}

	/**
	 * Get the Anilist media id from the malId
	 *
	 * @param string $malId
	 * @param string $type
	 * @return array
	 */
	private function getMediaIdFromMalId(string $malId, string $type = 'ANIME'): array
	{
		return $this->runQuery('MediaIdByMalId', [
			'id' => $malId,
			'type' => $type
		]);
	}
}