<?php declare(strict_types=1);
/**
 * Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     AnimeListClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2017  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */namespace Aviat\AnimeClient\Model;
use Aviat\AnimeClient\API\Kitsu\Enum\AnimeWatchingStatus;
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Json;

/**
 * Model for handling requests dealing with the anime list
 */
class Anime extends API {

	// Display constants
	const WATCHING = 'Watching';
	const PLAN_TO_WATCH = 'Plan to Watch';
	const DROPPED = 'Dropped';
	const ON_HOLD = 'On Hold';
	const COMPLETED = 'Completed';

	/**
	 * Map of API status constants to display constants
	 * @var array
	 */
	protected $const_map = [
		AnimeWatchingStatus::WATCHING => self::WATCHING,
		AnimeWatchingStatus::PLAN_TO_WATCH => self::PLAN_TO_WATCH,
		AnimeWatchingStatus::ON_HOLD => self::ON_HOLD,
		AnimeWatchingStatus::DROPPED => self::DROPPED,
		AnimeWatchingStatus::COMPLETED => self::COMPLETED,
	];

	/**
	 * Anime constructor.
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container) {
		parent::__construct($container);

		$this->kitsuModel = $container->get('kitsu-model');
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

		$output = [];
		$output[$this->const_map[$status]] = $data;

		return $output;
	}

	/**
	 * Get information about an anime from its id
	 *
	 * @param string $anime_id
	 * @return array
	 */
	public function getAnime($anime_id)
	{
		return $this->kitsuModel->getAnime($anime_id);
	}

	/**
	 * Search for anime by name
	 *
	 * @param string $name
	 * @return array
	 */
	public function search($name)
	{
		// $raw = $this->kitsuModel->search('anime', $name);
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

	public function createLibraryItem(array $data): bool
	{
		return $this->kitsuModel->createListItem($data);
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

	public function deleteLibraryItem($id): bool
	{
		return $this->kitsuModel->deleteListItem($id);
	}
}
// End of AnimeModel.php