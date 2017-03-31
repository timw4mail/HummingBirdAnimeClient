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

namespace Aviat\AnimeClient\API\Kitsu;

use function Amp\{all, wait};

use Amp\Artax\{Client, Request};
use Aviat\AnimeClient\API\{
	CacheTrait,
	JsonAPI,
	Kitsu as K,
	ParallelAPIRequest
};
use Aviat\AnimeClient\API\Enum\{
	AnimeWatchingStatus\Title,
	AnimeWatchingStatus\Kitsu as KitsuWatchingStatus,
	MangaReadingStatus\Kitsu as KitsuReadingStatus
};
use Aviat\AnimeClient\API\Mapping\{AnimeWatchingStatus, MangaReadingStatus};
use Aviat\AnimeClient\API\Kitsu\Transformer\{
	AnimeTransformer,
	AnimeListTransformer,
	MangaTransformer,
	MangaListTransformer
};
use Aviat\Ion\{Di\ContainerAware, Json};

/**
 * Kitsu API Model
 */
class Model {
	use CacheTrait;
	use ContainerAware;
	use KitsuTrait;

	const FULL_TRANSFORMED_LIST_CACHE_KEY = 'kitsu-full-organized-anime-list';

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
	 * Get the userid for a username from Kitsu
	 *
	 * @param string $username
	 * @return string
	 */
	public function getUserIdByUsername(string $username = NULL): string
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
	 * Get information about a character
	 *
	 * @param string $slug
	 * @return array
	 */
	public function getCharacter(string $slug): array
	{
		// @todo catch non-existent characters and show 404
		$data = $this->getRequest('/characters', [
			'query' => [
				'filter' => [
					'name' => $slug
				],
				// 'include' => 'primaryMedia,castings'
			]
		]);

		return $data;
	}

	/**
	 * Get profile information for the configured user
	 *
	 * @param string $username
	 * @return array
	 */
	public function getUserData(string $username): array
	{
		// $userId = $this->getUserIdByUsername($username);
		$data = $this->getRequest("/users", [
			'query' => [
				'filter' => [
					'name' => $username,
				],
				'fields' => [
					// 'anime' => 'slug,name,canonicalTitle',
					'characters' => 'slug,name,image'
				],
				'include' => 'waifu,pinnedPost,blocks,linkedAccounts,profileLinks,profileLinks.profileLinkSite,mediaFollows,userRoles,favorites.item'
			]
		]);

		return $data;
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
	 * Find a media item on Kitsu by its associated MAL id
	 *
	 * @param string $malId
	 * @param string $type "anime" or "manga"
	 * @return string
	 */
	public function getKitsuIdFromMALId(string $malId, string $type="anime"): string
	{
		$options = [
			'query' => [
				'filter' => [
					'external_site' => "myanimelist/{$type}",
					'external_id' => $malId
				],
				'fields' => [
					'media' => 'id,slug'
				],
				'include' => 'media'
			]
		];

		$raw = $this->getRequest('mappings', $options);

		return $raw['included'][0]['id'];
	}

	// -------------------------------------------------------------------------
	// ! Anime-specific methods
	// -------------------------------------------------------------------------

	/**
	 * Get information about a particular anime
	 *
	 * @param string $slug
	 * @return array
	 */
	public function getAnime(string $slug): array
	{
		$baseData = $this->getRawMediaData('anime', $slug);

		if (empty($baseData))
		{
			return [];
		}

		$transformed = $this->animeTransformer->transform($baseData);
		$transformed['included'] = $baseData['included'];
		return $transformed;
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
	 * Get the anime list for the configured user
	 *
	 * @param string $status - The watching status to filter the list with
	 * @return array
	 */
	public function getAnimeList(string $status): array
	{
		$cacheItem = $this->cache->getItem("kitsu-anime-list-{$status}");

		if ( ! $cacheItem->isHit())
		{
			$data = $this->getRawAnimeList($status) ?? [];

			// Bail out on no data
			if (empty($data))
			{
				$cacheItem->set([]);
				$cacheItem->save();
				return $cacheItem->get();
			}

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

	/**
	 * Get the number of anime list items
	 *
	 * @param string $status - Optional status to filter by
	 * @return int
	 */
	public function getAnimeListCount(string $status = '') : int
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

		if ( ! empty($status))
		{
			$options['query']['filter']['status'] = $status;
		}

		$response = $this->getRequest('library-entries', $options);

		return $response['meta']['count'];
	}

	/**
	 * Get the full anime list
	 *
	 * @param array $options
	 * @return array
	 */
	public function getFullAnimeList(array $options = [
		'include' => 'anime.mappings'
	]): array
	{
		$status = $options['filter']['status'] ?? '';
		$count = $this->getAnimeListCount($status);
		$size = 100;
		$pages = ceil($count / $size);

		$requester = new ParallelAPIRequest();

		// Set up requests
		for ($i = 0; $i < $pages; $i++)
		{
			$offset = $i * $size;
			$requester->addRequest($this->getPagedAnimeList($size, $offset, $options));
		}

		$responses = $requester->makeRequests();
		$output = [];

		foreach($responses as $response)
		{
			$data = Json::decode($response->getBody());
			$output = array_merge_recursive($output, $data);
		}

		return $output;
	}

	/**
	 * Get all the anine entries, that are organized for output to html
	 *
	 * @return array
	 */
	public function getFullOrganizedAnimeList(): array
	{
		$output = [];

		$statuses = KitsuWatchingStatus::getConstList();

		foreach ($statuses as $key => $status)
		{
			$mappedStatus = AnimeWatchingStatus::KITSU_TO_TITLE[$status];
			$output[$mappedStatus] = $this->getAnimeList($status) ?? [];
		}

		return $output;
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

		if ( ! array_key_exists('included', $data))
		{
			return NULL;
		}

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
	 * Get the full anime list in paginated form
	 *
	 * @param int $limit
	 * @param int $offset
	 * @param array $options
	 * @return Request
	 */
	public function getPagedAnimeList(int $limit = 100, int $offset = 0, array $options = [
		'include' => 'anime.mappings'
	]): Request
	{
		$defaultOptions = [
			'filter' => [
				'user_id' => $this->getUserIdByUsername($this->getUsername()),
				'media_type' => 'Anime'
			],
			'page' => [
				'offset' => $offset,
				'limit' => $limit
			],
			'sort' => '-updated_at'
		];
		$options = array_merge($defaultOptions, $options);

		return $this->setUpRequest('GET', 'library-entries', ['query' => $options]);
	}

	/**
	 * Get the raw (unorganized) anime list for the configured user
	 *
	 * @param string $status - The watching status to filter the list with
	 * @return array
	 */
	public function getRawAnimeList(string $status): array
	{

		$options = [
			'filter' => [
				'user_id' => $this->getUserIdByUsername($this->getUsername()),
				'media_type' => 'Anime',
				'status' => $status,
			],
			'include' => 'media,media.genres,media.mappings,anime.streamingLinks',
			'sort' => '-updated_at'
		];

		return $this->getFullAnimeList($options);
	}

	// -------------------------------------------------------------------------
	// ! Manga-specific methods
	// -------------------------------------------------------------------------

	/**
	 * Get information about a particular manga
	 *
	 * @param string $slug
	 * @return array
	 */
	public function getManga(string $slug): array
	{
		$baseData = $this->getRawMediaData('manga', $slug);

		if (empty($baseData))
		{
			return [];
		}

		$transformed = $this->mangaTransformer->transform($baseData);
		$transformed['included'] = $baseData['included'];
		return $transformed;
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
				'include' => 'media,media.genres,media.mappings',
				'page' => [
					'offset' => $offset,
					'limit' => $limit
				],
				'sort' => '-updated_at'
			]
		];

		$cacheItem = $this->cache->getItem("kitsu-manga-list-{$status}");

		if ( ! $cacheItem->isHit())
		{
			$data = $this->getRequest('library-entries', $options);

			$included = JsonAPI::organizeIncludes($data['included']);
			$included = JsonAPI::inlineIncludedRelationships($included, 'manga');

			foreach($data['data'] as $i => &$item)
			{
				$item['included'] = $included;
			}

			$transformed = $this->mangaListTransformer->transformCollection($data['data']);

			$cacheItem->set($transformed);
			$cacheItem->save();
		}

		return $cacheItem->get();
	}

	/**
	 * Get the number of manga list items
	 *
	 * @param string $status - Optional status to filter by
	 * @return int
	 */
	public function getMangaListCount(string $status = '') : int
	{
		$options = [
			'query' => [
				'filter' => [
					'user_id' => $this->getUserIdByUsername(),
					'media_type' => 'Manga'
				],
				'page' => [
					'limit' => 1
				],
				'sort' => '-updated_at'
			]
		];

		if ( ! empty($status))
		{
			$options['query']['filter']['status'] = $status;
		}

		$response = $this->getRequest('library-entries', $options);

		return $response['meta']['count'];
	}

	/**
	 * Get the full manga list
	 *
	 * @param array $options
	 * @return array
	 */
	public function getFullMangaList(array $options = [
		'include' => 'manga.mappings'
	]): array
	{
		$status = $options['filter']['status'] ?? '';
		$count = $this->getMangaListCount($status);
		$size = 100;
		$pages = ceil($count / $size);

		$requester = new ParallelAPIRequest();

		// Set up requests
		for ($i = 0; $i < $pages; $i++)
		{
			$offset = $i * $size;
			$requester->addRequest($this->getPagedMangaList($size, $offset, $options));
		}

		$responses = $requester->makeRequests();
		$output = [];

		foreach($responses as $response)
		{
			$data = Json::decode($response->getBody());
			$output = array_merge_recursive($output, $data);
		}

		return $output;
	}

	/**
	 * Get all Manga lists
	 *
	 * @return array
	 */
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
	 * Get the full manga list in paginated form
	 *
	 * @param int $limit
	 * @param int $offset
	 * @param array $options
	 * @return Request
	 */
	public function getPagedMangaList(int $limit = 100, int $offset = 0, array $options = [
		'include' => 'manga.mappings'
	]): Request
	{
		$defaultOptions = [
			'filter' => [
				'user_id' => $this->getUserIdByUsername($this->getUsername()),
				'media_type' => 'Manga'
			],
			'page' => [
				'offset' => $offset,
				'limit' => $limit
			],
			'sort' => '-updated_at'
		];
		$options = array_merge($defaultOptions, $options);

		return $this->setUpRequest('GET', 'library-entries', ['query' => $options]);
	}

	/**
	 * Get the mal id for the manga represented by the kitsu id
	 * to enable updating MyAnimeList
	 *
	 * @param string $kitsuAnimeId The id of the anime on Kitsu
	 * @return string|null Returns the mal id if it exists, otherwise null
	 */
	public function getMalIdForManga(string $kitsuMangaId)
	{
		$options = [
			'query' => [
				'include' => 'mappings'
			]
		];
		$data = $this->getRequest("manga/{$kitsuMangaId}", $options);
		$mappings = array_column($data['included'], 'attributes');

		foreach($mappings as $map)
		{
			if ($map['externalSite'] === 'myanimelist/manga')
			{
				return $map['externalId'];
			}
		}

		return NULL;
	}

	// -------------------------------------------------------------------------
	// ! Generic API calls
	// -------------------------------------------------------------------------

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

	/**
	 * Get the kitsu username from config
	 *
	 * @return string
	 */
	private function getUsername(): string
	{
		return $this->getContainer()
			->get('config')
			->get(['kitsu_username']);
	}

	/**
	 * Get the raw data for the anime id
	 *
	 * @param string $type
	 * @param string $id
	 * @return array
	 */
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

		if (empty($data['data']))
		{
			return [];
		}

		$baseData = $data['data']['attributes'];
		$baseData['included'] = $data['included'];
		return $baseData;
	}

	/**
	 * Get media item by slug
	 *
	 * @param string $type
	 * @param string $slug
	 * @return array
	 */
	private function getRawMediaData(string $type, string $slug): array
	{
		$options = [
			'query' => [
				'filter' => [
					'slug' => $slug
				],
				'fields' => [
					'characters' => 'slug,name,image'
				],
				'include' => ($type === 'anime')
					? 'genres,mappings,streamingLinks,animeCharacters.character'
					: 'genres,mappings,mangaCharacters.character,castings.character',
			]
		];

		$data = $this->getRequest($type, $options);

		if (empty($data['data']))
		{
			return [];
		}

		$baseData = $data['data'][0]['attributes'];
		$baseData['included'] = $data['included'];
		return $baseData;
	}
}