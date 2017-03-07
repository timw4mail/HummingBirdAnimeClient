<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2017  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Model;

use Aviat\AnimeClient\API\ParallelAPIRequest;
use Aviat\AnimeClient\API\Mapping\AnimeWatchingStatus;
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Json;

/**
 * Model for handling requests dealing with the anime list
 */
class Anime extends API {
	/**
	 * Model for making requests to Kitsu API
	 *
	 * @var \Aviat\AnimeClient\API\Kitsu\Model
	 */
	protected $kitsuModel;

	/**
	 * Model for making requests to MAL API
	 *
	 * @var \Aviat\AnimeClient\API\MAL\Model
	 */
	protected $malModel;

	/**
	 * Whether to use the MAL api
	 *
	 * @var boolean
	 */
	protected $useMALAPI;

	/**
	 * Anime constructor.
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$config = $container->get('config');
		$this->kitsuModel = $container->get('kitsu-model');
		$this->malModel = $container->get('mal-model');

		$this->useMALAPI = $config->get(['use_mal_api']) === TRUE;
	}

	/**
	 * Get a category out of the full list
	 *
	 * @param string $status
	 * @return array
	 */
	public function getList($status)
	{
		$data = $this->kitsuModel->getAnimeList($status);
		$this->sortByName($data, 'anime');

		$key = AnimeWatchingStatus::KITSU_TO_TITLE[$status];

		$output = [];
		$output[$key] = $data;

		return $output;
	}

	public function getAllLists()
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
	 * @return array
	 */
	public function getAnime($slug)
	{
		return $this->kitsuModel->getAnime($slug);
	}

	/**
	 * Get anime by its kitsu id
	 *
	 * @param string $animeId
	 * @return array
	 */
	public function getAnimeById($animeId)
	{
		return $this->kitsuModel->getAnimeById($animeId);
	}

	/**
	 * Search for anime by name
	 *
	 * @param string $name
	 * @return array
	 */
	public function search($name)
	{
		return $this->kitsuModel->search('anime', $name);
	}

	/**
	 * Get information about a specific list item
	 * for editing/updating that item
	 *
	 * @param string $itemId
	 * @return array
	 */
	public function getLibraryItem(string $itemId): array
	{
		return $this->kitsuModel->getListItem($itemId);
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

		if ($this->useMALAPI)
		{
			$malData = $data;
			$malId = $this->kitsuModel->getMalIdForAnime($malData['id']);

			if ( ! is_null($malId))
			{
				$malData['id'] = $malId;
				$requester->addRequest($this->malModel->createListItem($malData), 'mal');
			}
		}

		$requester->addRequest($this->kitsuModel->createListItem($data), 'kitsu');

		$results = $requester->makeRequests(TRUE);

		return count($results[1]) > 0;
	}

	/**
	 * Update a list entry
	 *
	 * @param array $data
	 * @return array
	 */
	public function updateLibraryItem(array $data): array
	{
		$requester = new ParallelAPIRequest();

		if ($this->useMALAPI)
		{
			$requester->addRequest($this->malModel->updateListItem($data), 'mal');
		}

		$requester->addRequest($this->kitsuModel->updateListItem($data), 'kitsu');

		$results = $requester->makeRequests(TRUE);

		return [
			'body' => Json::decode($results[1]['kitsu']->getBody()),
			'statusCode' => $results[1]['kitsu']->getStatus()
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

		if ($this->useMALAPI && ! is_null($malId))
		{
			$requester->addRequest($this->malModel->deleteListItem($malId), 'MAL');
		}

		$requester->addRequest($this->kitsuModel->deleteListItem($id), 'kitsu');

		$results = $requester->makeRequests(TRUE);

		return count($results[1]) > 0;
	}
}