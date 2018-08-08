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
 * @copyright   2015 - 2018  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Model;

use Aviat\AnimeClient\API\{
	Enum\MangaReadingStatus\Title,
	Mapping\MangaReadingStatus,
	ParallelAPIRequest
};
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Json;

/**
 * Model for handling requests dealing with the manga list
 */
final class Manga extends API
{
	/**
	 * Model for making requests to Kitsu API
	 * @var \Aviat\AnimeClient\API\Kitsu\Model
	 */
	protected $kitsuModel;

	/**
	 * Model for making requests to MAL API
	 * @var \Aviat\AnimeClient\API\MAL\Model
	 */
	protected $malModel;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface $container
	 * @throws \Aviat\Ion\Di\ContainerException
	 * @throws \Aviat\Ion\Di\NotFoundException
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->kitsuModel = $container->get('kitsu-model');
		$this->malModel = $container->get('mal-model');

		$config = $container->get('config');
		$this->useMALAPI = $config->get(['use_mal_api']) === TRUE;
	}

	/**
	 * Get a category out of the full list
	 *
	 * @param string $status
	 * @return array
	 */
	public function getList($status): array
	{
		if ($status === 'All')
		{
			return $this->kitsuModel->getFullOrganizedMangaList();
		}

		$APIstatus = MangaReadingStatus::TITLE_TO_KITSU[$status];
		$data = $this->kitsuModel->getMangaList($APIstatus);
		return $this->mapByStatus($data)[$status];
	}

	/**
	 * Get the details of a manga
	 *
	 * @param string $manga_id
	 * @return array
	 */
	public function getManga($manga_id): array
	{
		return $this->kitsuModel->getManga($manga_id);
	}

	/**
	 * Get anime by its kitsu id
	 *
	 * @param string $animeId
	 * @return array
	 */
	public function getMangaById(string $animeId): array
	{
		return $this->kitsuModel->getMangaById($animeId);
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
	 * Create a new manga list item
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
			$malId = $this->kitsuModel->getMalIdForManga($malData['id']);

			if ($malId !== NULL)
			{
				$malData['id'] = $malId;
				$requester->addRequest($this->malModel->createListItem($malData, 'manga'), 'mal');
			}
		}

		$requester->addRequest($this->kitsuModel->createListItem($data), 'kitsu');

		$results = $requester->makeRequests();

		return count($results) > 0;
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
			$requester->addRequest($this->malModel->updateListItem($data, 'manga'), 'mal');
		}

		$requester->addRequest($this->kitsuModel->updateListItem($data), 'kitsu');

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

		if ($this->useMALAPI && $malId !== NULL)
		{
			$requester->addRequest($this->malModel->deleteListItem($malId, 'manga'), 'MAL');
		}

		$requester->addRequest($this->kitsuModel->deleteListItem($id), 'kitsu');

		$results = $requester->makeRequests();

		return count($results) > 0;
	}

	/**
	 * Search for anime by name
	 *
	 * @param string $name
	 * @return array
	 */
	public function search($name): array
	{
		return $this->kitsuModel->search('manga', $name);
	}

	/**
	 * Map transformed anime data to be organized by reading status
	 *
	 * @param array $data
	 * @return array
	 */
	private function mapByStatus(array $data): array
	{
		$output = [
			Title::READING => [],
			Title::PLAN_TO_READ => [],
			Title::ON_HOLD => [],
			Title::DROPPED => [],
			Title::COMPLETED => [],
		];

		foreach ($data as &$entry) {
			$statusMap = MangaReadingStatus::KITSU_TO_TITLE;
			$key = $statusMap[$entry['reading_status']];
			$output[$key][] = $entry;
		}

		foreach ($output as &$val) {
			$this->sortByName($val, 'manga');
		}

		return $output;
	}
}
// End of MangaModel.php