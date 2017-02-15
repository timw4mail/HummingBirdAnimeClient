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

use function Amp\some;
use function Amp\wait;
use Amp\Artax\Client;
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

	protected $kitsuModel;

	protected $malModel;

	protected $useMALAPI;

	/**
	 * Anime constructor.
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container) {
		parent::__construct($container);

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

		$output = [];
		$output[$this->const_map[$status]] = $data;

		return $output;
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
		$requests = [];

		if ($this->useMALAPI)
		{
			$malData = $data;
			$malId = $this->kitsuModel->getMalIdForAnime($malData['id']);

			if ( ! is_null($malId))
			{
				$malData['id'] = $malId;
				$requests['mal'] = $this->malModel->createListItem($malData);
			}
		}

		$requests['kitsu'] = $this->kitsuModel->createListItem($data);

		$promises = (new Client)->requestMulti($requests);

		$results = wait(some($promises));

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
		$requests = []; 
		
		if ($this->useMALAPI)
		{
			$requests['mal'] = $this->malModel->updateListItem($data);
		}

		$requests['kitsu'] = $this->kitsuModel->updateListItem($data);
		
		$promises = (new Client)->requestMulti($requests);

		$results = wait(some($promises));
		
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
	public function deleteLibraryItem(string $id, string $malId = null): bool
	{
		$requests = [];

		if ($this->useMALAPI && ! is_null($malId))
		{
			$requests['mal'] = $this->malModel->deleteListItem($malId);
		}

		$requests['kitsu'] = $this->kitsuModel->deleteListItem($id);

		$results = wait(some((new Client)->requestMulti($requests)));

		return count($results[1]) > 0;
	}
}
// End of AnimeModel.php