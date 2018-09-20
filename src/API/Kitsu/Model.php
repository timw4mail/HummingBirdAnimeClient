<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
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

namespace Aviat\AnimeClient\API\Kitsu;

use function Amp\Promise\wait;

use Amp\Artax\Request;
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
	AnimeTransformer,
	AnimeListTransformer,
	MangaTransformer,
	MangaListTransformer
};
use Aviat\AnimeClient\Types\{
	AbstractType,
	Anime,
	FormItem,
	FormItemData,
	AnimeListItem,
	MangaPage
};
use Aviat\Ion\{Di\ContainerAware, Json};

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
	private $animeListTransformer;

	/**
	 * @var AnimeTransformer
	 */
	private $animeTransformer;

	/**
	 * @var ListItem
	 */
	private $listItem;

	/**
	 * @var MangaTransformer
	 */
	private $mangaTransformer;

	/**
	 * @var MangaListTransformer
	 */
	private $mangaListTransformer;

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
	 * @return bool|array
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
		$data = Json::decode(wait($response->getBody()));

		if (array_key_exists('access_token', $data))
		{
			return $data;
		}

		if (array_key_exists('error', $data))
		{
			dump($data['error']);
			dump($response);
			die();
		}

		return FALSE;
	}

	/**
	 * Extend the current session with a refresh token
	 *
	 * @param string $token
	 * @return bool|array
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

		$data = Json::decode(wait($response->getBody()));

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
		$data = $this->getRequest('characters', [
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
		$data = $this->getRequest("users", [
			'query' => [
				'filter' => [
					'name' => $username,
				],
				'fields' => [
					// 'anime' => 'slug,name,canonicalTitle',
					'characters' => 'slug,name,image'
				],
				'include' => 'waifu,pinnedPost,blocks,linkedAccounts,profileLinks,profileLinks.profileLinkSite,userRoles,favorites.item'
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
	public function getKitsuIdFromMALId(string $malId, string $type="anime")
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
			return new Anime();
		}

		$transformed = $this->animeTransformer->transform($baseData);
		$transformed['included'] = JsonAPI::organizeIncluded($baseData['included']);
		return $transformed;
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
	public function getPagedAnimeList(int $limit, int $offset = 0, array $options = [
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
			return new MangaPage([]);
		}

		$transformed = $this->mangaTransformer->transform($baseData);
		$transformed['included'] = $baseData['included'];
		return $transformed;
	}

	/**
	 * Get information about a particular manga
	 *
	 * @param string $mangaId
	 * @return array
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
			$data = $this->getRequest('library-entries', $options) ?? [];

			// Bail out on no data
			if (empty($data) || ( ! array_key_exists('included', $data)))
			{
				$cacheItem->set([]);
				$cacheItem->save();
				return $cacheItem->get();
			}

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
	public function getPagedMangaList(int $limit, int $offset = 0, array $options = [
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
	 * @param string $kitsuMangaId The id of the manga on Kitsu
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
	 * @return mixed
	 */
	public function getListItem(string $listId)
	{
		$baseData = $this->listItem->get($listId);
		$included = JsonAPI::organizeIncludes($baseData['included']);


		switch (TRUE)
		{
			case array_key_exists('anime', $included): // in_array('anime', array_keys($included)):
				$included = JsonAPI::inlineIncludedRelationships($included, 'anime');
				$baseData['data']['included'] = $included;
				return $this->animeListTransformer->transform($baseData['data']);

			case array_key_exists('manga', $included): // in_array('manga', array_keys($included)):
				$included = JsonAPI::inlineIncludedRelationships($included, 'manga');
				$baseData['data']['included'] = $included;
				$baseData['data']['manga'] = $baseData['included'][0];
				return $this->mangaListTransformer->transform($baseData['data']);

			default:
				return $baseData['data'];
		}
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
					'characters' => 'slug,name,image'
				],
				'include' => ($type === 'anime')
					? 'categories,mappings,streamingLinks,animeCharacters.character'
					: 'categories,mappings,mangaCharacters.character,castings.character',
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