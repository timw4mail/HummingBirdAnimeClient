<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Model;

use Aviat\AnimeClient\API\Anilist\Model as AnilistModel;
use Aviat\AnimeClient\API\Kitsu\Model as KitsuModel;
use Aviat\AnimeClient\API\ParallelAPIRequest;
use Aviat\AnimeClient\API\Mapping\AnimeWatchingStatus;
use Aviat\AnimeClient\Types\{
	Anime as AnimeType,
	FormItem,
	AnimeListItem
};
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Json;

use Throwable;

/**
 * Model for handling requests dealing with the anime list
 */
class Anime extends API {

	/**
	 * Is the Anilist API enabled?
	 *
	 * @var boolean
	 */
	protected $anilistEnabled;

	/**
	 * Model for making requests to Anilist API
	 *
	 * @var AnilistModel
	 */
	protected $anilistModel;

	/**
	 * Model for making requests to Kitsu API
	 *
	 * @var KitsuModel
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

		$config = $container->get('config');
		$this->anilistEnabled = (bool) $config->get(['anilist', 'enabled']);
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
		return $this->kitsuModel->search('anime', urldecode($name));
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

		if (\is_array($array['notes']))
		{
			$array['notes'] = '';
		}

		return new AnimeListItem($array);
	}

	/**
	 * Add an anime to your list
	 *
	 * @param array $data
	 * @return bool
	 * @throws Throwable
	 */
	public function createLibraryItem(array $data): bool
	{
		$requester = new ParallelAPIRequest();
		$requester->addRequest($this->kitsuModel->createListItem($data), 'kitsu');

		if ($this->anilistEnabled && $data['mal_id'] !== null)
		{
			// If can't map MAL id, this will be null
			$maybeRequest = $this->anilistModel->createListItem($data, 'ANIME');
			if ($maybeRequest !== NULL)
			{
				$requester->addRequest($maybeRequest, 'anilist');
			}
		}

		$results = $requester->makeRequests();

		return count($results) > 0;
	}

	/**
	 * Increment progress for the specified anime
	 *
	 * @param FormItem $data
	 * @return array
	 * @throws Throwable
	 */
	public function incrementLibraryItem(FormItem $data): array
	{
		$requester = new ParallelAPIRequest();
		$requester->addRequest($this->kitsuModel->incrementListItem($data), 'kitsu');

		if (( ! empty($data['mal_id'])) && $this->anilistEnabled)
		{
			// If can't map MAL id, this will be null
			$maybeRequest = $this->anilistModel->incrementListItem($data, 'ANIME');
			if ($maybeRequest !== NULL)
			{
				$requester->addRequest($maybeRequest, 'anilist');
			}
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
	 * @param FormItem $data
	 * @return array
	 * @throws Throwable
	 */
	public function updateLibraryItem(FormItem $data): array
	{
		$requester = new ParallelAPIRequest();
		$requester->addRequest($this->kitsuModel->updateListItem($data), 'kitsu');

		if (( ! empty($data['mal_id'])) && $this->anilistEnabled)
		{
			// If can't map MAL id, this will be null
			$maybeRequest = $this->anilistModel->updateListItem($data, 'ANIME');
			if ($maybeRequest !== NULL)
			{
				$requester->addRequest($maybeRequest, 'anilist');
			}
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
	 * @throws Throwable
	 */
	public function deleteLibraryItem(string $id, string $malId = NULL): bool
	{
		$requester = new ParallelAPIRequest();
		$requester->addRequest($this->kitsuModel->deleteListItem($id), 'kitsu');

		if ($this->anilistEnabled && $malId !== null)
		{
			// If can't map MAL id, this will be null
			$maybeRequest = $this->anilistModel->deleteListItem($malId, 'ANIME');
			if ($maybeRequest !== NULL)
			{
				$requester->addRequest($maybeRequest, 'anilist');
			}
		}

		$results = $requester->makeRequests();

		return count($results) > 0;
	}
}