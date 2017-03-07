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

namespace Aviat\AnimeClient\API\Kitsu;

use function Amp\{all, wait};

use Amp\Artax\{Client, Request};
use Aviat\AnimeClient\API\{
	CacheTrait,
	JsonAPI,
	Kitsu as K,
	Mapping\AnimeWatchingStatus,
	Mapping\MangaReadingStatus
};
use Aviat\AnimeClient\API\Enum\{
	AnimeWatchingStatus\Title,
	MangaReadingStatus\Kitsu as KitsuReadingStatus
};
use Aviat\AnimeClient\API\Kitsu\Transformer\{
	AnimeTransformer,
	AnimeListTransformer,
	MangaTransformer,
	MangaListTransformer
};
use Aviat\Ion\Di\ContainerAware;
use Aviat\Ion\Json;

/**
 * Kitsu API Model
 */
class Model {
	use CacheTrait;
	use ContainerAware;
	use KitsuTrait;

	const FULL_TRANSFORMED_LIST_CACHE_KEY = 'FullOrganizedAnimeList';

	/**
	 * Class to map anime list items
	 * to a common format used by
	 * templates
	 *
	 * @var AnimeListTransformer
	 */
	protected $animeListTransformer;

	/**
	 * @var AnimeTransformer
	 */
	protected $animeTransformer;

	/**
	 * @var ListItem
	 */
	protected $listItem;

	/**
	 * @var MangaTransformer
	 */
	protected $mangaTransformer;

	/**
	 * @var MangaListTransformer
	 */
	protected $mangaListTransformer;


	/**
	 * Constructor
	 *
	 * @param ListItem $listItem
	 */
	public function __construct(ListItem $listItem)
	{
		$this->animeTransformer = new AnimeTransformer();
		$this->animeListTransformer = new AnimeListTransformer();
		$this->listItem = $listItem;
		$this->mangaTransformer = new MangaTransformer();
		$this->mangaListTransformer = new MangaListTransformer();
	}

	/**
	 * Get the userid for a username from Kitsu
	 *
	 * @param string $username
	 * @return string
	 */
	public function getUserIdByUsername(string $username = NULL)
	{
		if (is_null($username))
		{
			$username = $this->getUsername();
		}

		$cacheItem = $this->cache->getItem(K::AUTH_USER_ID_KEY);

		if ( ! $cacheItem->isHit())
		{
			$data = $this->getRequest('users', [
				'query' => [
					'filter' => [
						'name' => $username
					]
				]
			]);

			$cacheItem->set($data['data'][0]['id']);
			$cacheItem->save();
		}

		return $cacheItem->get();
	}

	/**
	 * Get the access token from the Kitsu API
	 *
	 * @param string $username
	 * @param string $password
	 * @return bool|string
	 */
	public function authenticate(string $username, string $password)
	{
		$response = $this->getResponse('POST', K::AUTH_URL, [
			'headers' => [],
			'form_params' => [
				'grant_type' => 'password',
				'username' => $username,
				'password' => $password
			]
		]);

		$data = Json::decode((string)$response->getBody());

		if (array_key_exists('access_token', $data))
		{
			return $data;
		}

		return FALSE;
	}

	/**
	 * Get information about a particular anime
	 *
	 * @param string $slug
	 * @return array
	 */
	public function getAnime(string $slug): array
	{
		// @TODO catch non-existent anime
		$baseData = $this->getRawMediaData('anime', $slug);
		return $this->animeTransformer->transform($baseData);
	}

	/**
	 * Get information about a particular anime
	 *
	 * @param string $animeId
	 * @return array
	 */
	public function getAnimeById(string $animeId): array
	{
		$baseData = $this->getRawMediaDataById('anime', $animeId);
		return $this->animeTransformer->transform($baseData);
	}

	/**
	 * Get the mal id for the anime represented by the kitsu id
	 * to enable updating MyAnimeList
	 *
	 * @param string $kitsuAnimeId The id of the anime on Kitsu
	 * @return string|null Returns the mal id if it exists, otherwise null
	 */
	public function getMalIdForAnime(string $kitsuAnimeId)
	{
		$options = [
			'query' => [
				'include' => 'mappings'
			]
		];
		$data = $this->getRequest("anime/{$kitsuAnimeId}", $options);
		$mappings = array_column($data['included'], 'attributes');

		foreach($mappings as $map)
		{
			if ($map['externalSite'] === 'myanimelist/anime')
			{
				return $map['externalId'];
			}
		}

		return NULL;
	}

	/**
	 * Get information about a particular manga
	 *
	 * @param string $mangaId
	 * @return array
	 */
	public function getManga(string $mangaId): array
	{
		$baseData = $this->getRawMediaData('manga', $mangaId);
		return $this->mangaTransformer->transform($baseData);
	}
	
	/**
	 * Get the number of anime list items
	 *
	 * @return int
	 */
	public function getAnimeListCount() : int
	{
		$options = [
			'query' => [
				'filter' => [
					'user_id' => $this->getUserIdByUsername(),
					'media_type' => 'Anime'
				],
				'page' => [
					'limit' => 1
				],
				'sort' => '-updated_at'
			]
		];
		
		$response = $this->getRequest('library-entries', $options);
		
		return $response['meta']['count'];
		
	}

	/**
	 * Get the full anime list in paginated form
	 *
	 * @param int $limit
	 * @param int $offset
	 * @param string $include
	 * @return Request
	 */
	public function getPagedAnimeList(int $limit = 100, int $offset = 0, $include='anime.mappings'): Request
	{
		$options = [
			'query' => [
				'filter' => [
					'user_id' => $this->getUserIdByUsername($this->getUsername()),
					'media_type' => 'Anime'
				],
				'include' => $include,
				'page' => [
					'offset' => $offset,
					'limit' => $limit
				],
				'sort' => '-updated_at'
			]
		];
		
		return $this->setUpRequest('GET', 'library-entries', $options);
	}
	
	/**
	 * Get the full anime list
	 *
	 * @param string $include
	 * @return Request
	 */
	public function getFullAnimeList($include = 'anime.mappings')
	{
		$count = $this->getAnimeListCount();
		$size = 75;
		$pages = ceil($count / $size);
		
		$requests = [];
		
		// Set up requests
		for ($i = 0; $i < $pages; $i++)
		{
			$offset = $i * $size;
			$requests[] = $this->getPagedAnimeList($size, $offset, $include);
		}
		
		$promiseArray = (new Client())->requestMulti($requests);

		$responses = wait(all($promiseArray));
		$output = [];

		foreach($responses as $response)
		{
			$data = Json::decode($response->getBody());
			$output = array_merge_recursive($output, $data);
		}

		return $output;
	}

	/**
	 * Get the raw (unorganized) anime list for the configured user
	 *
	 * @param string $status - The watching status to filter the list with
	 * @param int $limit - The number of list entries to fetch for a page
	 * @param int $offset - The page offset
	 * @return array
	 */
	public function getRawAnimeList(string $status, int $limit = 600, int $offset = 0): array
	{
		$options = [
			'query' => [
				'filter' => [
					'user_id' => $this->getUserIdByUsername($this->getUsername()),
					'media_type' => 'Anime',
					'status' => $status,
				],
				'include' => 'media,media.genres,media.mappings,anime.streamingLinks',
				'page' => [
					'offset' => $offset,
					'limit' => $limit
				],
				'sort' => '-updated_at'
			]
		];

		return $this->getRequest('library-entries', $options);
	}

	public function getFullOrganizedAnimeList(): array
	{
		$cacheItem = $this->cache->getItem(self::FULL_TRANSFORMED_LIST_CACHE_KEY);

		if ( ! $cacheItem->isHit())
		{
			$output = [
				Title::WATCHING => [],
				Title::PLAN_TO_WATCH => [],
				Title::ON_HOLD => [],
				Title::DROPPED => [],
				Title::COMPLETED => []
			];
			$statusMap = AnimeWatchingStatus::KITSU_TO_TITLE;

			$data = $this->getFullAnimeList('media,media.genres,media.mappings,anime.streamingLinks');
			$included = JsonAPI::organizeIncludes($data['included']);
			$included = JsonAPI::inlineIncludedRelationships($included, 'anime');

			foreach($data['data'] as $i => &$item)
			{
				$item['included'] = $included;
			}
			$transformed = $this->animeListTransformer->transformCollection($data['data']);

			foreach($transformed as $item)
			{
				$key = $statusMap[$item['watching_status']];
				$output[$key][] = $item;
			}

			$cacheItem->set($output);
			$cacheItem->save();
		}

		return $cacheItem->get();
	}

	/**
	 * Get the anime list for the configured user
	 *
	 * @param string $status - The watching status to filter the list with
	 * @param int $limit - The number of list entries to fetch for a page
	 * @param int $offset - The page offset
	 * @return array
	 */
	public function getAnimeList(string $status, int $limit = 600, int $offset = 0): array
	{
		$cacheItem = $this->cache->getItem($this->getHashForMethodCall($this, __METHOD__, [$status]));

		if ( ! $cacheItem->isHit())
		{
			$data = $this->getRawAnimeList($status, $limit, $offset);
			$included = JsonAPI::organizeIncludes($data['included']);
			$included = JsonAPI::inlineIncludedRelationships($included, 'anime');

			foreach($data['data'] as $i => &$item)
			{
				$item['included'] = $included;
			}
			$transformed = $this->animeListTransformer->transformCollection($data['data']);

			$cacheItem->set($transformed);
			$cacheItem->save();
		}

		return $cacheItem->get();
	}

	public function getFullOrganizedMangaList(): array
	{
		$statuses = KitsuReadingStatus::getConstList();
		$output = [];
		foreach ($statuses as $status)
		{
			$mappedStatus = MangaReadingStatus::KITSU_TO_TITLE[$status];
			$output[$mappedStatus] = $this->getMangaList($status);
		}
		
		return $output;
	}

	/**
	 * Get the manga list for the configured user
	 *
	 * @param string $status - The reading status by which to filter the list
	 * @param int $limit - The number of list items to fetch per page
	 * @param int $offset - The page offset
	 * @return array
	 */
	public function getMangaList(string $status, int $limit = 200, int $offset = 0): array
	{
		$options = [
			'query' => [
				'filter' => [
					'user_id' => $this->getUserIdByUsername($this->getUsername()),
					'media_type' => 'Manga',
					'status' => $status,
				],
				'include' => 'media',
				'page' => [
					'offset' => $offset,
					'limit' => $limit
				],
				'sort' => '-updated_at'
			]
		];

		$cacheItem = $this->cache->getItem($this->getHashForMethodCall($this, __METHOD__, $options));

		if ( ! $cacheItem->isHit())
		{
			$data = $this->getRequest('library-entries', $options);
			$data = JsonAPI::inlineRawIncludes($data, 'manga');

			$transformed = $this->mangaListTransformer->transformCollection($data);

			$cacheItem->set($transformed);
			$cacheItem->save();
		}

		return $cacheItem->get();
	}

	/**
	 * Search for an anime or manga
	 *
	 * @param string $type - 'anime' or 'manga'
	 * @param string $query - name of the item to search for
	 * @return array
	 */
	public function search(string $type, string $query): array
	{
		$options = [
			'query' => [
				'filter' => [
					'text' => $query
				],
				'page' => [
					'offset' => 0,
					'limit' => 20
				],
			]
		];

		$raw = $this->getRequest($type, $options);

		foreach ($raw['data'] as &$item)
		{
			$item['attributes']['titles'] = K::filterTitles($item['attributes']);
			array_shift($item['attributes']['titles']);
		}

		return $raw;
	}

	/**
	 * Create a list item
	 *
	 * @param array $data
	 * @return Request
	 */
	public function createListItem(array $data): Request
	{
		$data['user_id'] = $this->getUserIdByUsername($this->getUsername());
		return $this->listItem->create($data);
	}

	/**
	 * Get the data for a specific list item, generally for editing
	 *
	 * @param string $listId - The unique identifier of that list item
	 * @return array
	 */
	public function getListItem(string $listId): array
	{
		$baseData = $this->listItem->get($listId);
		$included = JsonAPI::organizeIncludes($baseData['included']);


		switch (TRUE)
		{
			case in_array('anime', array_keys($included)):
				$included = JsonAPI::inlineIncludedRelationships($included, 'anime');
				$baseData['data']['included'] = $included;
				return $this->animeListTransformer->transform($baseData['data']);

			case in_array('manga', array_keys($included)):
				$included = JsonAPI::inlineIncludedRelationships($included, 'manga');
				$baseData['data']['included'] = $included;
				$baseData['data']['manga'] = $baseData['included'][0];
				return $this->mangaListTransformer->transform($baseData['data']);

			default:
				return $baseData['data'];
		}
	}

	/**
	 * Modify a list item
	 *
	 * @param array $data
	 * @return Request
	 */
	public function updateListItem(array $data): Request
	{
		return $this->listItem->update($data['id'], $data['data']);
	}

	/**
	 * Remove a list item
	 *
	 * @param string $id - The id of the list item to remove
	 * @return Request
	 */
	public function deleteListItem(string $id): Request
	{
		return $this->listItem->delete($id);
	}

	private function getUsername(): string
	{
		return $this->getContainer()
			->get('config')
			->get(['kitsu_username']);
	}

	private function getRawMediaDataById(string $type, string $id): array
	{
		$options = [
			'query' => [
				'include' => ($type === 'anime')
					? 'genres,mappings,streamingLinks'
					: 'genres,mappings',
			]
		];

		$data = $this->getRequest("{$type}/{$id}", $options);
		$baseData = $data['data']['attributes'];
		$baseData['included'] = $data['included'];
		return $baseData;
	}

	private function getRawMediaData(string $type, string $slug): array
	{
		$options = [
			'query' => [
				'filter' => [
					'slug' => $slug
				],
				'include' => ($type === 'anime')
					? 'genres,mappings,streamingLinks'
					: 'genres,mappings',
			]
		];

		$data = $this->getRequest($type, $options);
		$baseData = $data['data'][0]['attributes'];
		$baseData['included'] = $data['included'];
		return $baseData;
	}
}