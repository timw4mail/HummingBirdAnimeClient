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

namespace Aviat\AnimeClient\Model;

use Aviat\AnimeClient\API\ParallelAPIRequest;
use Aviat\AnimeClient\API\Mapping\AnimeWatchingStatus;
use Aviat\AnimeClient\Types\{
	Anime as AnimeType,
	AnimeFormItem,
	AnimeListItem
};
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Json;

/**
 * Model for handling requests dealing with the anime list
 */
class Anime extends API {

	/**
	 * Model for making requests to Anilist API
	 *
	 * @var \Aviat\AnimeClient\API\Anilist\Model
	 */
	protected $anilistModel;

	/**
	 * Model for making requests to Kitsu API
	 *
	 * @var \Aviat\AnimeClient\API\Kitsu\Model
	 */
	protected $kitsuModel;

	/**
	 * Anime constructor.
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->anilistModel = $container->get('anilist-model');
		$this->kitsuModel = $container->get('kitsu-model');
	}

	/**
	 * Get a category out of the full list
	 *
	 * @param string $status
	 * @return array
	 */
	public function getList($status): array
	{
		$data = $this->kitsuModel->getAnimeList($status);
		$this->sortByName($data, 'anime');

		$key = AnimeWatchingStatus::KITSU_TO_TITLE[$status];

		$output = [];
		$output[$key] = $data;

		return $output;
	}

	/**
	 * Get data for the 'all' anime page
	 *
	 * @return array
	 */
	public function getAllLists(): array
	{
		$data =  $this->kitsuModel->getFullOrganizedAnimeList();

		foreach($data as $section => &$list)
		{
			$this->sortByName($list, 'anime');
		}

		return $data;
	}

	/**
	 * Get information about an anime from its slug
	 *
	 * @param string $slug
	 * @return AnimeType
	 */
	public function getAnime(string $slug): AnimeType
	{
		return $this->kitsuModel->getAnime($slug);
	}

	/**
	 * Get anime by its kitsu id
	 *
	 * @param string $animeId
	 * @return AnimeType
	 */
	public function getAnimeById(string $animeId): AnimeType
	{
		return $this->kitsuModel->getAnimeById($animeId);
	}

	/**
	 * Search for anime by name
	 *
	 * @param string $name
	 * @return array
	 */
	public function search(string $name): array
	{
		return $this->kitsuModel->search('anime', $name);
	}

	/**
	 * Get information about a specific list item
	 * for editing/updating that item
	 *
	 * @param string $itemId
	 * @return AnimeListItem
	 */
	public function getLibraryItem(string $itemId): AnimeListItem
	{
		$item = $this->kitsuModel->getListItem($itemId);
		$array = $item->toArray();

		if ($item->mal_id !== NULL)
		{
			$anilistInfo = $this->anilistModel->getListItem($item['mal_id']);
			$array['anilist_item_id'] = $anilistInfo['id'];
		}

		return new AnimeListItem($array);
	}

	/**
	 * Add an anime to your list
	 *
	 * @param array $data
	 * @return bool
	 */
	public function createLibraryItem(array $data): bool
	{
		$requester = new ParallelAPIRequest();
		$requester->addRequest($this->kitsuModel->createListItem($data), 'kitsu');

		$results = $requester->makeRequests();

		return count($results) > 0;
	}

	/**
	 * Increment progress for the specified anime
	 *
	 * @param AnimeFormItem $data
	 * @return array
	 */
	public function incrementLibraryItem(AnimeFormItem $data): array
	{
		$requester = new ParallelAPIRequest();
		$requester->addRequest($this->kitsuModel->incrementListItem($data), 'kitsu');

		$array = $data->toArray();

		// @TODO Make sure Anilist integration is optional
		if (array_key_exists('mal_id', $array)) {
			$requester->addRequest($this->anilistModel->incrementListItem($data), 'anilist');
		}

		$results = $requester->makeRequests();

		$body = Json::decode($results['kitsu']);
		$statusCode = array_key_exists('error', $body) ? 400 : 200;

		return [
			'body' => Json::decode($results['kitsu']),
			'statusCode' => $statusCode
		];
	}

	/**
	 * Update a list entry
	 *
	 * @param AnimeFormItem $data
	 * @return array
	 */
	public function updateLibraryItem(AnimeFormItem $data): array
	{
		$requester = new ParallelAPIRequest();
		$requester->addRequest($this->kitsuModel->updateListItem($data), 'kitsu');

		$array = $data->toArray();

		// @TODO Make sure Anilist integration is optional
		if (array_key_exists('mal_id', $array))
		{
			$requester->addRequest($this->anilistModel->updateListItem($data), 'anilist');
		}

		$results = $requester->makeRequests();

		$body = Json::decode($results['kitsu']);
		$statusCode = array_key_exists('error', $body) ? 400: 200;

		return [
			'body' => Json::decode($results['kitsu']),
			'statusCode' => $statusCode
		];
	}

	/**
	 * Delete a list entry
	 *
	 * @param string $id
	 * @param string|null $malId
	 * @return bool
	 */
	public function deleteLibraryItem(string $id, string $malId = NULL): bool
	{
		$requester = new ParallelAPIRequest();
		$requester->addRequest($this->kitsuModel->deleteListItem($id), 'kitsu');

		$results = $requester->makeRequests();

		return count($results) > 0;
	}
}