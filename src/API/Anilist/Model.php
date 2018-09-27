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

use InvalidArgumentException;

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

	// -------------------------------------------------------------------------
	// ! Generic API calls
	// -------------------------------------------------------------------------

	/**
	 * Get user list data for syncing with Kitsu
	 *
	 * @param string $type
	 * @return array
	 * @throws \Aviat\Ion\Di\Exception\ContainerException
	 * @throws \Aviat\Ion\Di\Exception\NotFoundException
	 */
	public function getSyncList(string $type = 'anime'): array
	{
		$config = $this->container->get('config');
		$anilistUser = $config->get(['anilist', 'username']);

		if ( ! is_string($anilistUser))
		{
			throw new InvalidArgumentException('Anilist username is not defined in config');
		}

		return $this->runQuery('SyncUserList', [
			'name' => $anilistUser,
			'type' => $type,
		]);
	}

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

		$mediaId = $this->getMediaIdFromMalId($data['mal_id'], mb_strtoupper($type));

		if (empty($mediaId))
		{
			throw new InvalidArgumentException('Media id missing');
		}

		if ($type === 'anime')
		{
			$createData = [
				'id' => $mediaId,
				'status' => AnimeWatchingStatus::KITSU_TO_ANILIST[$data['status']],
			];
		}
		elseif ($type === 'manga')
		{
			$createData = [
				'id' => $mediaId,
				'status' => MangaReadingStatus::KITSU_TO_ANILIST[$data['status']],
			];
		}

		return $this->listItem->create($createData, $type);
	}

	/**
	 * Create a list item with all the relevant data
	 *
	 * @param array $data
	 * @param string $type
	 * @return Request
	 */
	public function createFullListItem(array $data, string $type = 'anime'): Request
	{
		$createData = $data['data'];
		$mediaId = $this->getMediaIdFromMalId($data['mal_id']);
		
		$createData['id'] = $mediaId;
		
		return $this->listItem->createFull($createData);
	}

	/**
	 * Get the data for a specific list item, generally for editing
	 *
	 * @param string $malId - The unique identifier of that list item
	 * @return mixed
	 */
	public function getListItem(string $malId, string $type): array
	{
		$id = $this->getListIdFromMalId($malId, $type);

		$data = $this->listItem->get($id)['data'];

		return ($data !== null)
			? $data['MediaList']
			: [];
	}

	/**
	 * Increase the watch count for the current list item
	 *
	 * @param FormItem $data
	 * @return Request
	 */
	public function incrementListItem(FormItem $data, string $type): Request
	{
		$id = $this->getListIdFromMalId($data['mal_id'], $type);

		return $this->listItem->increment($id, $data['data']);
	}

	/**
	 * Modify a list item
	 *
	 * @param FormItem $data
	 * @param int [$id]
	 * @return Request
	 */
	public function updateListItem(FormItem $data, string $type): Request
	{
		$id = $this->getListIdFromMalId($data['mal_id'], mb_strtoupper($type));

		return $this->listItem->update($id, $data['data']);
	}

	/**
	 * Remove a list item
	 *
	 * @param string $malId - The id of the list item to remove
	 * @return Request
	 */
	public function deleteListItem(string $malId, string $type): Request
	{
		$item_id = $this->getListIdFromMalId($malId, $type);

		return $this->listItem->delete($item_id);
	}

	/**
	 * Get the id of the specific list entry from the malId
	 *
	 * @param string $malId
	 * @return string
	 */
	public function getListIdFromMalId(string $malId, string $type): ?string
	{
		$mediaId = $this->getMediaIdFromMalId($malId, $type);
		return $this->getListIdFromMediaId($mediaId);
	}
	
	/**
	 * Get the Anilist media id from its MAL id
	 * this way is more accurate than getting the list item id
	 * directly from the MAL id
	 */
	private function getListIdFromMediaId(string $mediaId)
	{
		$config = $this->container->get('config');
		$anilistUser = $config->get(['anilist', 'username']);
		
		$info = $this->runQuery('ListItemIdByMediaId', [
			'id' => $mediaId,
			'userName' => $anilistUser,
		]);
		
		/* dump([
			'media_id' => $mediaId,
			'userName' => $anilistUser,
			'response' => $info,
		]);
		die(); */

		return (string)$info['data']['MediaList']['id'];
	}

	/**
	 * Get the Anilist media id from the malId
	 *
	 * @param string $malId
	 * @param string $type
	 * @return string
	 */
	private function getMediaIdFromMalId(string $malId, string $type = 'ANIME'): ?string
	{
		$info = $this->runQuery('MediaIdByMalId', [
			'id' => $malId,
			'type' => mb_strtoupper($type),
		]);
		
		/* dump([
			'mal_id' => $malId,
			'response' => $info,
		]);
		die(); */

		return (string)$info['data']['Media']['id'];
	}
}