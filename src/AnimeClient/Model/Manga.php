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

use Aviat\AnimeClient\API\{
	Enum\MangaReadingStatus\Title,
	Mapping\MangaReadingStatus,
	ParallelAPIRequest
};
use Aviat\AnimeClient\Types\{
	FormItem,
	MangaListItem,
	MangaPage
};
use Aviat\AnimeClient\API\{Anilist, Kitsu};
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Json;

use Throwable;

/**
 * Model for handling requests dealing with the manga list
 */
class Manga extends API {
	/**
	 * Is the Anilist API enabled?
	 *
	 * @var boolean
	 */
	protected bool $anilistEnabled;

	/**
	 * Model for making requests to the Anilist API
	 * @var Anilist\Model
	 */
	protected Anilist\Model $anilistModel;

	/**
	 * Model for making requests to Kitsu API
	 * @var Kitsu\Model
	 */
	protected Kitsu\Model $kitsuModel;

	/**
	 * Constructor
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->anilistModel = $container->get('anilist-model');
		$this->kitsuModel = $container->get('kitsu-model');

		$config = $container->get('config');
		$this->anilistEnabled = (bool)$config->get(['anilist', 'enabled']);
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
			$data = $this->kitsuModel->getFullOrganizedMangaList();
			foreach($data as &$section)
			{
				$this->sortByName($section, 'manga');
			}

			return $data;
		}

		$APIstatus = MangaReadingStatus::TITLE_TO_KITSU[$status];
		$data = $this->mapByStatus($this->kitsuModel->getMangaList($APIstatus));
		$this->sortByName($data[$status], 'manga');
		return $data[$status];
	}

	/**
	 * Get the details of a manga
	 *
	 * @param string $manga_id
	 * @return MangaPage
	 */
	public function getManga($manga_id): MangaPage
	{
		return $this->kitsuModel->getManga($manga_id);
	}

	/**
	 * Get anime by its kitsu id
	 *
	 * @param string $animeId
	 * @return MangaPage
	 */
	public function getMangaById(string $animeId): MangaPage
	{
		return $this->kitsuModel->getMangaById($animeId);
	}

	/**
	 * Get information about a specific list item
	 * for editing/updating that item
	 *
	 * @param string $itemId
	 * @return MangaListItem
	 */
	public function getLibraryItem(string $itemId): MangaListItem
	{
		return $this->kitsuModel->getListItem($itemId);
	}

	/**
	 * Create a new manga list item
	 *
	 * @param array $data
	 * @return bool
	 * @throws Throwable
	 */
	public function createLibraryItem(array $data): bool
	{
		$requester = new ParallelAPIRequest();
		$requester->addRequest($this->kitsuModel->createListItem($data), 'kitsu');

		if ($this->anilistEnabled && array_key_exists('mal_id', $data))
		{
			$requester->addRequest($this->anilistModel->createListItem($data, 'MANGA'), 'anilist');
		}

		$results = $requester->makeRequests();

		return count($results) > 0;
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

		$array = $data->toArray();

		if ($this->anilistEnabled && array_key_exists('mal_id', $array))
		{
			$requester->addRequest($this->anilistModel->updateListItem($data, 'MANGA'), 'anilist');
		}

		$results = $requester->makeRequests();
		$body = Json::decode($results['kitsu']);
		$statusCode = array_key_exists('errors', $body) ? 400: 200;

		return [
			'body' => Json::decode($results['kitsu']),
			'statusCode' => $statusCode
		];
	}

	/**
	 * Increase the progress of a list entry
	 *
	 * @param FormItem $data
	 * @return array
	 * @throws Throwable
	 */
	public function incrementLibraryItem(FormItem $data): array
	{
		$requester = new ParallelAPIRequest();
		$requester->addRequest($this->kitsuModel->incrementListItem($data), 'kitsu');

		$array = $data->toArray();

		if ($this->anilistEnabled && array_key_exists('mal_id', $array))
		{
			$requester->addRequest($this->anilistModel->incrementListItem($data, 'MANGA'), 'anilist');
		}

		$results = $requester->makeRequests();

		$body = Json::decode($results['kitsu']);
		$statusCode = array_key_exists('errors', $body)
			? $body['errors'][0]['status']
			: 200;

		return [$body, $statusCode];
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
			$requester->addRequest($this->anilistModel->deleteListItem($malId, 'MANGA'), 'anilist');
		}

		$results = $requester->makeRequests();

		return count($results) > 0;
	}

	/**
	 * Search for manga by name
	 *
	 * @param string $name
	 * @return array
	 */
	public function search($name): array
	{
		return $this->kitsuModel->search('manga', $name);
	}

	/**
	 * Get recent reading history
	 *
	 * @return array
	 */
	public function getHistory(): array
	{
		return $this->kitsuModel->getMangaHistory();
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

		unset($entry);

		return $output;
	}
}
// End of MangaModel.php