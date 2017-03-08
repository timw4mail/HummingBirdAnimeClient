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
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Model;

use Aviat\AnimeClient\API\Enum\MangaReadingStatus\Title;
use Aviat\AnimeClient\API\Mapping\MangaReadingStatus;
use Aviat\Ion\Di\ContainerInterface;

/**
 * Model for handling requests dealing with the manga list
 */
class Manga extends API
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
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->kitsuModel = $container->get('kitsu-model');
		$this->malModel = $container->get('mal-model');
	}

	/**
	 * Get a category out of the full list
	 *
	 * @param string $status
	 * @return array
	 */
	public function getList($status)
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
	public function getManga($manga_id)
	{
		return $this->kitsuModel->getManga($manga_id);
	}

	/**
	 * Create a new manga list item
	 *
	 * @param array $data
	 * @return bool
	 */
	public function createLibraryItem(array $data): bool
	{
		return $this->kitsuModel->createListItem($data);
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
	 * Update a list entry
	 *
	 * @param array $data
	 * @return array
	 */
	public function updateLibraryItem(array $data): array
	{
		return $this->kitsuModel->updateListItem($data);
	}

	/**
	 * Remove a list entry
	 *
	 * @param string $itemId
	 * @return bool
	 */
	public function deleteLibraryItem(string $itemId): bool
	{
		return $this->kitsuModel->deleteListItem($itemId);
	}

	/**
	 * Search for anime by name
	 *
	 * @param string $name
	 * @return array
	 */
	public function search($name)
	{
		return $this->kitsuModel->search('manga', $name);
	}

	/**
	 * Map transformed anime data to be organized by reading status
	 *
	 * @param array $data
	 * @return array
	 */
	private function mapByStatus($data)
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