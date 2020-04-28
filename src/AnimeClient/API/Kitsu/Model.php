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

namespace Aviat\AnimeClient\API\Kitsu;

use function Amp\Promise\wait;

use Amp\Http\Client\Request;
use Aviat\AnimeClient\API\{
	CacheTrait,
	JsonAPI,
	Kitsu as K,
	ParallelAPIRequest
};
use Aviat\AnimeClient\API\Enum\{
	AnimeWatchingStatus\Kitsu as KitsuWatchingStatus,
	MangaReadingStatus\Kitsu as KitsuReadingStatus
};
use Aviat\AnimeClient\API\Mapping\{AnimeWatchingStatus, MangaReadingStatus};
use Aviat\AnimeClient\API\Kitsu\Transformer\{
	AnimeHistoryTransformer,
	AnimeTransformer,
	AnimeListTransformer,
	MangaHistoryTransformer,
	MangaTransformer,
	MangaListTransformer
};
use Aviat\AnimeClient\Types\{
	Anime,
	FormItem,
	MangaPage
};

use Aviat\Banker\Exception\InvalidArgumentException;
use Aviat\Ion\{Di\ContainerAware, Json};

use ReflectionException;
use Throwable;

/**
 * Kitsu API Model
 */
final class Model {
	use CacheTrait;
	use ContainerAware;
	use KitsuTrait;

	private const LIST_PAGE_SIZE = 100;

	/**
	 * Class to map anime list items
	 * to a common format used by
	 * templates
	 *
	 * @var AnimeListTransformer
	 */
	private AnimeListTransformer $animeListTransformer;

	/**
	 * @var AnimeTransformer
	 */
	private AnimeTransformer $animeTransformer;

	/**
	 * @var ListItem
	 */
	private ListItem $listItem;

	/**
	 * @var MangaTransformer
	 */
	private MangaTransformer $mangaTransformer;

	/**
	 * @var MangaListTransformer
	 */
	private MangaListTransformer $mangaListTransformer;

	/**
	 * Constructor
	 *
	 * @param ListItem $listItem
	 */
	public function __construct(ListItem $listItem)
	{
		$this->animeTransformer = new AnimeTransformer();
		$this->animeListTransformer = new AnimeListTransformer();
		$this->mangaTransformer = new MangaTransformer();
		$this->mangaListTransformer = new MangaListTransformer();

		$this->listItem = $listItem;
	}

	/**
	 * Get the access token from the Kitsu API
	 *
	 * @param string $username
	 * @param string $password
	 * @return bool|array
	 * @throws Throwable
	 */
	public function authenticate(string $username, string $password)
	{
		// K::AUTH_URL
		$response = $this->getResponse('POST', K::AUTH_URL, [
			'headers' => [
				'accept' => NULL,
				'Content-type' => 'application/x-www-form-urlencoded',
				'client_id' => NULL,
				'client_secret' => NULL
			],
			'form_params' => [
				'grant_type' => 'password',
				'username' => $username,
				'password' => $password
			]
		]);
		$data = Json::decode(wait($response->getBody()->buffer()));

		if (array_key_exists('error', $data))
		{
			dump($data['error']);
			dump($response);
			die();
		}

		if (array_key_exists('access_token', $data))
		{
			return $data;
		}

		return FALSE;
	}

	/**
	 * Extend the current session with a refresh token
	 *
	 * @param string $token
	 * @return bool|array
	 * @throws Throwable
	 */
	public function reAuthenticate(string $token)
	{
		$response = $this->getResponse('POST', K::AUTH_URL, [
			'headers' => [
				'Accept-encoding' => '*'

			],
			'form_params' => [
				'grant_type' => 'refresh_token',
				'refresh_token' => $token
			]
		]);

		$data = Json::decode(wait($response->getBody()->buffer()));

		if (array_key_exists('access_token', $data))
		{
			return $data;
		}

		return FALSE;
	}

	/**
	 * Retrieve the data for the anime watch history page
	 *
	 * @return array
	 * @throws InvalidArgumentException
	 * @throws Throwable
	 */
	public function getAnimeHistory(): array
	{
		$raw = $this->getRawHistoryList('anime');
		$organized = JsonAPI::organizeData($raw);
		$organized = array_filter($organized, fn ($item) => array_key_exists('relationships', $item));

		return (new AnimeHistoryTransformer())->transform($organized);
	}

	/**
	 * Retrieve the data for the manga read history page
	 *
	 * @return array
	 * @throws InvalidArgumentException
	 * @throws Throwable
	 */
	public function getMangaHistory(): array
	{
		$raw = $this->getRawHistoryList('manga');
		$organized = JsonAPI::organizeData($raw);
		$organized = array_filter($organized, fn ($item) => array_key_exists('relationships', $item));

		return (new MangaHistoryTransformer())->transform($organized);
	}

	/**
	 * Get the userid for a username from Kitsu
	 *
	 * @param string $username
	 * @return string
	 * @throws InvalidArgumentException
	 */
	public function getUserIdByUsername(string $username = NULL): string
	{
		if ($username === NULL)
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
		return $this->getRequest('characters', [
			'query' => [
				'filter' => [
					'slug' => $slug,
				],
				'fields' => [
					'anime' => 'canonicalTitle,titles,slug,posterImage',
					'manga' => 'canonicalTitle,titles,slug,posterImage'
				],
				'include' => 'castings.person,castings.media'
			]
		]);
	}

	/**
	 * Get information about a person
	 *
	 * @param string $id
	 * @return array
	 * @throws InvalidArgumentException
	 */
	public function getPerson(string $id): array
	{
		$cacheItem = $this->cache->getItem("kitsu-person-{$id}");

		if ( ! $cacheItem->isHit())
		{
			$data = $this->getRequest("people/{$id}", [
				'query' => [
					'filter' => [
						'id' => $id,
					],
					'fields' => [
						'characters' => 'canonicalName,slug,image',
						'characterVoices' => 'mediaCharacter',
						'anime' => 'canonicalTitle,titles,slug,posterImage',
						'manga' => 'canonicalTitle,titles,slug,posterImage',
						'mediaCharacters' => 'role,media,character',
						'mediaStaff' => 'role,media,person',
					],
					'include' => 'voices.mediaCharacter.media,voices.mediaCharacter.character,staff.media',
				],
			]);
			$cacheItem->set($data);
			$cacheItem->save();
		}

		return $cacheItem->get();
	}

	/**
	 * Get profile information for the configured user
	 *
	 * @param string $username
	 * @return array
	 */
	public function getUserData(string $username): array
	{
		return $this->getRequest('users', [
			'query' => [
				'filter' => [
					'name' => $username,
				],
				'fields' => [
					'anime' => 'slug,canonicalTitle,posterImage',
					'manga' => 'slug,canonicalTitle,posterImage',
					'characters' => 'slug,canonicalName,image',
				],
				'include' => 'waifu,favorites.item,stats'
			]
		]);
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
					'text' => $query,
				],
				'page' => [
					'offset' => 0,
					'limit' => 20
				],
				'include' => 'mappings'
			]
		];

		$raw = $this->getRequest($type, $options);
		$raw['included'] = JsonAPI::organizeIncluded($raw['included']);

		foreach ($raw['data'] as &$item)
		{
			$item['attributes']['titles'] = K::filterTitles($item['attributes']);
			array_shift($item['attributes']['titles']);

			// Map the mal_id if it exists for syncing with other APIs
			foreach($item['relationships']['mappings']['data'] as $rel)
			{
				$mapping = $raw['included']['mappings'][$rel['id']];

				if ($mapping['attributes']['externalSite'] === "myanimelist/{$type}")
				{
					$item['mal_id'] = $mapping['attributes']['externalId'];
				}
			}
		}

		return $raw;
	}

	/**
	 * Find a media item on Kitsu by its associated MAL id
	 *
	 * @param string $malId
	 * @param string $type "anime" or "manga"
	 * @return string|NULL
	 */
	public function getKitsuIdFromMALId(string $malId, string $type='anime'): ?string
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
				'include' => 'item'
			]
		];

		$raw = $this->getRequest('mappings', $options);

		if ( ! array_key_exists('included', $raw))
		{
			return NULL;
		}

		return $raw['included'][0]['id'];
	}

	// -------------------------------------------------------------------------
	// ! Anime-specific methods
	// -------------------------------------------------------------------------

	/**
	 * Get information about a particular anime
	 *
	 * @param string $slug
	 * @return Anime
	 */
	public function getAnime(string $slug): Anime
	{
		$baseData = $this->getRawMediaData('anime', $slug);

		if (empty($baseData))
		{
			return Anime::from([]);
		}

		return $this->animeTransformer->transform($baseData);
	}

	/**
	 * Get information about a particular anime
	 *
	 * @param string $animeId
	 * @return Anime
	 */
	public function getAnimeById(string $animeId): Anime
	{
		$baseData = $this->getRawMediaDataById('anime', $animeId);
		return $this->animeTransformer->transform($baseData);
	}

	/**
	 * Get the anime list for the configured user
	 *
	 * @param string $status - The watching status to filter the list with
	 * @return array
	 * @throws InvalidArgumentException
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
				return [];
			}

			$included = JsonAPI::organizeIncludes($data['included']);
			$included = JsonAPI::inlineIncludedRelationships($included, 'anime');

			foreach($data['data'] as $i => &$item)
			{
				$item['included'] = $included;
			}
			unset($item);
			$transformed = $this->animeListTransformer->transformCollection($data['data']);
			$keyed = [];

			foreach($transformed as $item)
			{
				$keyed[$item['id']] = $item;
			}

			$cacheItem->set($keyed);
			$cacheItem->save();
		}

		return $cacheItem->get();
	}

	/**
	 * Get the number of anime list items
	 *
	 * @param string $status - Optional status to filter by
	 * @return int
	 * @throws InvalidArgumentException
	 */
	public function getAnimeListCount(string $status = '') : int
	{
		$options = [
			'query' => [
				'filter' => [
					'user_id' => $this->getUserIdByUsername(),
					'kind' => 'anime'
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
	 * @throws InvalidArgumentException
	 * @throws Throwable
	 */
	public function getFullRawAnimeList(array $options = [
		'include' => 'anime.mappings'
	]): array
	{
		$status = $options['filter']['status'] ?? '';
		$count = $this->getAnimeListCount($status);
		$size = static::LIST_PAGE_SIZE;
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
			$data = Json::decode($response);
			$output[] = $data;
		}

		return array_merge_recursive(...$output);
	}

	/**
	 * Get all the anime entries, that are organized for output to html
	 *
	 * @return array
	 * @throws ReflectionException
	 * @throws InvalidArgumentException
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
	public function getMalIdForAnime(string $kitsuAnimeId): ?string
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
	 * @throws InvalidArgumentException
	 */
	public function getPagedAnimeList(int $limit, int $offset = 0, array $options = [
		'include' => 'anime.mappings'
	]): Request
	{
		$defaultOptions = [
			'filter' => [
				'user_id' => $this->getUserIdByUsername($this->getUsername()),
				'kind' => 'anime'
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
	 * @throws InvalidArgumentException
	 * @throws Throwable
	 */
	public function getRawAnimeList(string $status): array
	{
		$options = [
			'filter' => [
				'user_id' => $this->getUserIdByUsername($this->getUsername()),
				'kind' => 'anime',
				'status' => $status,
			],
			'include' => 'media,media.categories,media.mappings,anime.streamingLinks',
			'sort' => '-updated_at'
		];

		return $this->getFullRawAnimeList($options);
	}

	// -------------------------------------------------------------------------
	// ! Manga-specific methods
	// -------------------------------------------------------------------------

	/**
	 * Get information about a particular manga
	 *
	 * @param string $slug
	 * @return MangaPage
	 */
	public function getManga(string $slug): MangaPage
	{
		$baseData = $this->getRawMediaData('manga', $slug);

		if (empty($baseData))
		{
			return MangaPage::from([]);
		}

		return $this->mangaTransformer->transform($baseData);
	}

	/**
	 * Get information about a particular manga
	 *
	 * @param string $mangaId
	 * @return MangaPage
	 */
	public function getMangaById(string $mangaId): MangaPage
	{
		$baseData = $this->getRawMediaDataById('manga', $mangaId);
		return $this->mangaTransformer->transform($baseData);
	}

	/**
	 * Get the manga list for the configured user
	 *
	 * @param string $status - The reading status by which to filter the list
	 * @param int $limit - The number of list items to fetch per page
	 * @param int $offset - The page offset
	 * @return array
	 * @throws InvalidArgumentException
	 */
	public function getMangaList(string $status, int $limit = 200, int $offset = 0): array
	{
		$options = [
			'query' => [
				'filter' => [
					'user_id' => $this->getUserIdByUsername($this->getUsername()),
					'kind' => 'manga',
					'status' => $status,
				],
				'include' => 'media,media.categories,media.mappings',
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
			$data = $this->getRequest('library-entries', $options) ?? [];

			// Bail out on no data
			if (empty($data) || ( ! array_key_exists('included', $data)))
			{
				return [];
			}

			$included = JsonAPI::organizeIncludes($data['included']);
			$included = JsonAPI::inlineIncludedRelationships($included, 'manga');

			foreach($data['data'] as $i => &$item)
			{
				$item['included'] = $included;
			}
			unset($item);

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
	 * @throws InvalidArgumentException
	 */
	public function getMangaListCount(string $status = '') : int
	{
		$options = [
			'query' => [
				'filter' => [
					'user_id' => $this->getUserIdByUsername(),
					'kind' => 'manga'
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
	 * @throws InvalidArgumentException
	 * @throws Throwable
	 */
	public function getFullRawMangaList(array $options = [
		'include' => 'manga.mappings'
	]): array
	{
		$status = $options['filter']['status'] ?? '';
		$count = $this->getMangaListCount($status);
		$size = static::LIST_PAGE_SIZE;
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
			$data = Json::decode($response);
			$output[] = $data;
		}

		return array_merge_recursive(...$output);
	}

	/**
	 * Get all Manga lists
	 *
	 * @return array
	 * @throws ReflectionException
	 * @throws InvalidArgumentException
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
	 * @throws InvalidArgumentException
	 */
	public function getPagedMangaList(int $limit, int $offset = 0, array $options = [
		'include' => 'manga.mappings'
	]): Request
	{
		$defaultOptions = [
			'filter' => [
				'user_id' => $this->getUserIdByUsername($this->getUsername()),
				'kind' => 'manga'
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
	 * @param string $kitsuMangaId The id of the manga on Kitsu
	 * @return string|null Returns the mal id if it exists, otherwise null
	 */
	public function getMalIdForManga(string $kitsuMangaId): ?string
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
	 * @throws InvalidArgumentException
	 */
	public function createListItem(array $data): ?Request
	{
		$data['user_id'] = $this->getUserIdByUsername($this->getUsername());
		if ($data['id'] === NULL)
		{
			return NULL;
		}

		return $this->listItem->create($data);
	}

	/**
	 * Get the data for a specific list item, generally for editing
	 *
	 * @param string $listId - The unique identifier of that list item
	 * @return mixed
	 */
	public function getListItem(string $listId)
	{
		$baseData = $this->listItem->get($listId);
		$included = JsonAPI::organizeIncludes($baseData['included']);

		if (array_key_exists('anime', $included))
		{
			$included = JsonAPI::inlineIncludedRelationships($included, 'anime');
			$baseData['data']['included'] = $included;
			return $this->animeListTransformer->transform($baseData['data']);
		}

		if (array_key_exists('manga', $included))
		{
			$included = JsonAPI::inlineIncludedRelationships($included, 'manga');
			$baseData['data']['included'] = $included;
			$baseData['data']['manga'] = $baseData['included'][0];
			return $this->mangaListTransformer->transform($baseData['data']);
		}

		return $baseData['data'];
	}

	/**
	 * Increase the progress count for a list item
	 *
	 * @param FormItem $data
	 * @return Request
	 */
	public function incrementListItem(FormItem $data): Request
	{
		return $this->listItem->increment($data['id'], $data['data']);
	}

	/**
	 * Modify a list item
	 *
	 * @param FormItem $data
	 * @return Request
	 */
	public function updateListItem(FormItem $data): Request
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
	 * Get the aggregated pages of anime or manga history
	 *
	 * @param string $type
	 * @param int $entries
	 * @return array
	 * @throws InvalidArgumentException
	 * @throws Throwable
	 */
	protected function getRawHistoryList(string $type = 'anime', int $entries = 120): array
	{
		$size = 20;
		$pages = ceil($entries / $size);

		$requester = new ParallelAPIRequest();

		// Set up requests
		for ($i = 0; $i < $pages; $i++)
		{
			$offset = $i * $size;
			$requester->addRequest($this->getRawHistoryPage($type, $offset, $size));
		}

		$responses = $requester->makeRequests();
		$output = [];

		foreach($responses as $response)
		{
			$data = Json::decode($response);
			$output[] = $data;
		}

		return array_merge_recursive(...$output);
	}

	/**
	 * Retrieve one page of the anime or manga history
	 *
	 * @param string $type
	 * @param int $offset
	 * @param int $limit
	 * @return Request
	 * @throws InvalidArgumentException
	 */
	protected function getRawHistoryPage(string $type, int $offset, int $limit = 20): Request
	{
		return $this->setUpRequest('GET', 'library-events', [
			'query' => [
				'filter' => [
					'kind' => 'progressed,updated',
					'userId' => $this->getUserIdByUsername($this->getUsername()),
				],
				'page' => [
					'offset' => $offset,
					'limit' => $limit,
				],
				'fields' => [
					'anime' => 'canonicalTitle,titles,slug,posterImage',
					'manga' => 'canonicalTitle,titles,slug,posterImage',
					'libraryEntry' => 'reconsuming,reconsumeCount',
				],
				'sort' => '-updated_at',
				'include' => 'anime,manga,libraryEntry',
			],
		]);
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
	 * Get the raw data for the anime/manga id
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
					? 'categories,mappings,streamingLinks'
					: 'categories,mappings',
			]
		];

		$data = $this->getRequest("{$type}/{$id}", $options);

		if (empty($data['data']))
		{
			return [];
		}

		$baseData = $data['data']['attributes'];
		$baseData['id'] = $id;
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
					'categories' => 'slug,title',
					'characters' => 'slug,name,image',
					'mappings' => 'externalSite,externalId',
					'animeCharacters' => 'character,role',
					'mediaCharacters' => 'character,role',
				],
				'include' => ($type === 'anime')
					? 'staff,staff.person,categories,mappings,streamingLinks,animeCharacters.character,characters.character'
					: 'staff,staff.person,categories,mappings,characters.character',
			]
		];

		$data = $this->getRequest($type, $options);

		if (empty($data['data']))
		{
			return [];
		}

		$baseData = $data['data'][0]['attributes'];
		$baseData['id'] = $data['data'][0]['id'];
		$baseData['included'] = $data['included'];
		return $baseData;
	}
}