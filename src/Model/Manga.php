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
use Aviat\AnimeClient\Types\{
	MangaFormItem,
	MangaListItem,
	MangaPage
};
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Json;

/**
 * Model for handling requests dealing with the manga list
 */
class Manga extends API {
	/**
	 * Model for making requests to Kitsu API
	 * @var \Aviat\AnimeClient\API\Kitsu\Model
	 */
	protected $kitsuModel;

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
	 */
	public function createLibraryItem(array $data): bool
	{
		$requester = new ParallelAPIRequest();
		$requester->addRequest($this->kitsuModel->createListItem($data), 'kitsu');

		$results = $requester->makeRequests();

		return count($results) > 0;
	}

	/**
	 * Update a list entry
	 *
	 * @param MangaFormItem $data
	 * @return array
	 */
	public function updateLibraryItem(MangaFormItem $data): array
	{
		$requester = new ParallelAPIRequest();
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

		unset($entry);

		return $output;
	}
}
// End of MangaModel.php