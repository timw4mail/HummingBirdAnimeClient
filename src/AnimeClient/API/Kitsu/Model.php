<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.1
 *
 * @copyright   2015 - 2023  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu;

use Aviat\AnimeClient\API\Kitsu\Transformer\{
	AnimeHistoryTransformer,
	AnimeListTransformer,
	AnimeTransformer,
	LibraryEntryTransformer,
	MangaHistoryTransformer,
	MangaListTransformer,
	MangaTransformer
};
use Aviat\AnimeClient\API\{
	CacheTrait,
	Enum\AnimeWatchingStatus\Kitsu as KitsuWatchingStatus,
	Enum\MangaReadingStatus\Kitsu as KitsuReadingStatus,
	Kitsu\Enum\MediaStatus,
	Mapping\AnimeWatchingStatus,
	Mapping\MangaReadingStatus
};
use Aviat\AnimeClient\Enum\MediaType;
use Aviat\AnimeClient\Kitsu as K;
use Aviat\AnimeClient\Types\{Anime, MangaPage};
use Aviat\AnimeClient\Types\{AnimeListItem, MangaListItem};
use Aviat\Ion\{
	Di\ContainerAware,
	Json
};
use Generator;
use function Aviat\AnimeClient\getApiClient;
use const Aviat\AnimeClient\SESSION_SEGMENT;

/**
 * Kitsu API Model
 */
final class Model
{
	use CacheTrait;
	use ContainerAware;
	use RequestBuilderTrait;
	use MutationTrait;

	protected const LIST_PAGE_SIZE = 100;

	protected AnimeTransformer $animeTransformer;
	protected MangaTransformer $mangaTransformer;

	/**
	 * Constructor
	 */
	public function __construct(protected ListItem $listItem)
	{
		$this->animeTransformer = new AnimeTransformer();
		$this->mangaTransformer = new MangaTransformer();
	}

	/**
	 * Get the access token from the Kitsu API
	 */
	public function authenticate(string $username, string $password): array|false
	{
		// K::AUTH_URL
		$response = $this->requestBuilder->getResponse('POST', K::AUTH_URL, [
			'headers' => [
				'accept' => NULL,
				'Content-type' => 'application/x-www-form-urlencoded',
				'client_id' => NULL,
				'client_secret' => NULL,
			],
			'form_params' => [
				'grant_type' => 'password',
				'username' => $username,
				'password' => $password,
			],
		]);
		$data = Json::decode($response->getBody()->buffer());

		if (array_key_exists('error', $data))
		{
			dump([
				'method' => self::class . '\\' . __METHOD__,
				'error' => $data['error'],
				'response' => $response,
			]);

			exit();
		}

		if (array_key_exists('access_token', $data))
		{
			return $data;
		}

		return FALSE;
	}

	/**
	 * Extend the current session with a refresh token
	 */
	public function reAuthenticate(string $token): array|false
	{
		$response = $this->requestBuilder->getResponse('POST', K::AUTH_URL, [
			'headers' => [
				'accept' => NULL,
				'Content-type' => 'application/x-www-form-urlencoded',
				'Accept-encoding' => '*',
			],
			'form_params' => [
				'grant_type' => 'refresh_token',
				'refresh_token' => $token,
			],
		]);
		$data = Json::decode($response->getBody()->buffer());

		if (array_key_exists('error', $data))
		{
			dump([
				'method' => self::class . '\\' . __METHOD__,
				'error' => $data['error'],
				'response' => $response,
			]);

			exit();
		}

		if (array_key_exists('access_token', $data))
		{
			return $data;
		}

		return FALSE;
	}

	/**
	 * Get the userid for a username from Kitsu
	 */
	public function getUserIdByUsername(?string $username = NULL): string
	{
		if ($username === NULL)
		{
			$username = $this->getUsername();
		}

		return $this->getCached(K::AUTH_USER_ID_KEY, function (string $username) {
			$data = $this->requestBuilder->runQuery('GetUserId', [
				'slug' => $username,
			]);

			return $data['data']['findProfileBySlug']['id'] ?? NULL;
		}, [$username]);
	}

	/**
	 * Get information about a character
	 *
	 * @return mixed[]
	 */
	public function getCharacter(string $slug): array
	{
		return $this->requestBuilder->runQuery('CharacterDetails', [
			'slug' => $slug,
		]);
	}

	/**
	 * Get information about a person
	 *
	 * @return mixed[]
	 */
	public function getPerson(string $slug): array
	{
		return $this->getCached("kitsu-person-{$slug}", fn () => $this->requestBuilder->runQuery('PersonDetails', [
			'slug' => $slug,
		]));
	}

	/**
	 * Get profile information for the configured user
	 *
	 * @return mixed[]
	 */
	public function getUserData(string $username): array
	{
		return $this->requestBuilder->runQuery('UserDetails', [
			'slug' => $username,
		]);
	}

	// -------------------------------------------------------------------------
	// ! Anime-specific methods
	// -------------------------------------------------------------------------
	/**
	 * Get information about a particular anime
	 */
	public function getAnime(string $slug): Anime
	{
		$baseData = $this->requestBuilder->runQuery('AnimeDetails', [
			'slug' => $slug,
		]);

		if (empty($baseData))
		{
			return Anime::from([]);
		}

		return $this->animeTransformer->transform($baseData);
	}

	public function getRandomAnime(): Anime
	{
		$baseData = $this->requestBuilder->runQuery('RandomMedia', [
			'type' => 'ANIME',
		]);

		return $this->animeTransformer->transform($baseData);
	}

	public function getRandomLibraryAnime(string $status): Anime
	{
		// @TODO
		return Anime::from([]);
	}

	/**
	 * Get information about a particular anime
	 */
	public function getAnimeById(string $animeId): Anime
	{
		$baseData = $this->requestBuilder->runQuery('AnimeDetailsById', [
			'id' => $animeId,
		]);

		return $this->animeTransformer->transform($baseData);
	}

	/**
	 * Retrieve the data for the anime watch history page
	 *
	 * @return mixed[]
	 */
	public function getAnimeHistory(): array
	{
		$key = K::ANIME_HISTORY_LIST_CACHE_KEY;
		$list = $this->cache->get($key, NULL);

		if ($list === NULL)
		{
			$raw = $this->getHistoryList();

			$list = (new AnimeHistoryTransformer())->transform($raw);

			$this->cache->set($key, $list);
		}

		return $list;
	}

	/**
	 * Get the anime list for the configured user
	 *
	 * @param string $status - The watching status to filter the list with
	 * @return mixed[]
	 */
	public function getAnimeList(string $status): array
	{
		$key = "kitsu-anime-list-{$status}";

		$list = $this->cache->get($key, NULL);

		if ($list === NULL)
		{
			$data = $this->getList(MediaType::ANIME, $status);

			// Bail out on no data
			if (empty($data))
			{
				return [];
			}

			$transformer = new AnimeListTransformer();
			$transformed = $transformer->transformCollection($data);
			$keyed = [];

			foreach ($transformed as $item)
			{
				$keyed[$item['id']] = $item;
			}

			$list = $keyed;
			$this->cache->set($key, $list);
		}

		return $list;
	}

	/**
	 * Get the number of anime list items
	 *
	 * @param string $status - Optional status to filter by
	 */
	public function getAnimeListCount(string $status = ''): int
	{
		return $this->getListCount(MediaType::ANIME, $status);
	}

	/**
	 * Get all the anime entries, that are organized for output to html
	 *
	 * @return array<string, array>
	 */
	public function getFullOrganizedAnimeList(): array
	{
		$output = [];

		$statuses = KitsuWatchingStatus::getConstList();

		foreach ($statuses as $status)
		{
			$mappedStatus = AnimeWatchingStatus::KITSU_TO_TITLE[$status];
			$output[$mappedStatus] = $this->getAnimeList($status);
		}

		return $output;
	}

	// -------------------------------------------------------------------------
	// ! Manga-specific methods
	// -------------------------------------------------------------------------
	/**
	 * Get information about a particular manga
	 */
	public function getManga(string $slug): MangaPage
	{
		$baseData = $this->requestBuilder->runQuery('MangaDetails', [
			'slug' => $slug,
		]);

		if (empty($baseData))
		{
			return MangaPage::from([]);
		}

		return $this->mangaTransformer->transform($baseData);
	}

	public function getRandomManga(): MangaPage
	{
		$baseData = $this->requestBuilder->runQuery('RandomMedia', [
			'type' => 'MANGA',
		]);

		return $this->mangaTransformer->transform($baseData);
	}

	/**
	 * Get information about a particular manga
	 */
	public function getMangaById(string $mangaId): MangaPage
	{
		$baseData = $this->requestBuilder->runQuery('MangaDetailsById', [
			'id' => $mangaId,
		]);

		return $this->mangaTransformer->transform($baseData);
	}

	/**
	 * Retrieve the data for the manga read history page
	 *
	 * @return mixed[]
	 */
	public function getMangaHistory(): array
	{
		$key = K::MANGA_HISTORY_LIST_CACHE_KEY;
		$list = $this->cache->get($key, NULL);

		if ($list === NULL)
		{
			$raw = $this->getHistoryList();
			$list = (new MangaHistoryTransformer())->transform($raw);

			$this->cache->set($key, $list);
		}

		return $list;
	}

	/**
	 * Get the manga list for the configured user
	 *
	 * @param string $status - The reading status by which to filter the list
	 * @return mixed[]
	 */
	public function getMangaList(string $status): array
	{
		$key = "kitsu-manga-list-{$status}";

		$list = $this->cache->get($key, NULL);

		if ($list === NULL)
		{
			$data = $this->getList(MediaType::MANGA, $status);

			// Bail out on no data
			if (empty($data))
			{
				return [];
			}

			$transformer = new MangaListTransformer();
			$transformed = $transformer->transformCollection($data);
			$keyed = [];

			foreach ($transformed as $item)
			{
				$keyed[$item['id']] = $item;
			}

			$list = $keyed;
			$this->cache->set($key, $list);
		}

		return $list;
	}

	/**
	 * Get the number of manga list items
	 *
	 * @param string $status - Optional status to filter by
	 */
	public function getMangaListCount(string $status = ''): int
	{
		return $this->getListCount(MediaType::MANGA, $status);
	}

	/**
	 * Get all Manga lists
	 *
	 * @return array<string, mixed[]>
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

	// ------------------------------------------------------------------------
	// Base methods
	// ------------------------------------------------------------------------
	/**
	 * Search for an anime or manga
	 *
	 * @param string $type - 'anime' or 'manga'
	 * @param string $query - name of the item to search for
	 * @return array<int, array<string, mixed>>
	 */
	public function search(string $type, string $query): array
	{
		$uType = ucfirst(strtolower($type));
		$raw = $this->requestBuilder->runQuery("Search{$uType}", [
			'query' => $query,
		]);

		$nodes = $raw['data']["search{$uType}ByTitle"]['nodes'];
		$data = [];

		foreach ($nodes as $item)
		{
			$searchItem = [
				'id' => $item['id'],
				'slug' => $item['slug'],
				'coverImage' => K::getPosterImage($item),
				'canonicalTitle' => $item['titles']['canonical'],
				'titles' => array_values(K::getTitles($item['titles'])),
				'libraryEntry' => $item['myLibraryEntry'],
			];

			// Search for External mapping
			if (is_array($item['mappings']['nodes']))
			{
				foreach ($item['mappings']['nodes'] as $mapping)
				{
					if ($mapping['externalSite'] === 'ANILIST_' . strtoupper($type))
					{
						$searchItem['anilist_id'] = $mapping['externalId'];
					}

					if ($mapping['externalSite'] === 'MYANIMELIST_' . strtoupper($type))
					{
						$searchItem['mal_id'] = $mapping['externalId'];
					}
				}
			}

			$data[] = $searchItem;
		}

		return $data;
	}

	/**
	 * Find a media item on Kitsu by its associated MAL id
	 *
	 * @param string $type "anime" or "manga"
	 */
	public function getKitsuIdFromMALId(string $malId, string $type = 'anime'): ?string
	{
		$raw = $this->requestBuilder->runQuery('GetIdByMapping', [
			'id' => $malId,
			'site' => strtoupper("MYANIMELIST_{$type}"),
		]);

		return $raw['data']['lookupMapping']['id'] ?? NULL;
	}

	/**
	 * Get the data for a specific list item, generally for editing
	 *
	 * @param string $listId - The unique identifier of that list item
	 */
	public function getListItem(string $listId): AnimeListItem|MangaListItem|array
	{
		$baseData = $this->listItem->get($listId);
		if ( ! isset($baseData['data']['findLibraryEntryById']))
		{
			// We need to get the errors...
			return $baseData;
		}

		return (new LibraryEntryTransformer())->transform($baseData['data']['findLibraryEntryById']);
	}

	/**
	 * @return mixed[]
	 */
	public function getThumbList(string $type): array
	{
		return $this->getZippedListPerStatus('GetLibraryThumbs', $type);
	}

	/**
	 * Get the data to sync Kitsu anime/manga list with another API
	 *
	 * @return mixed[]
	 */
	public function getSyncList(string $type): array
	{
		return $this->getZippedListPerStatus('GetSyncLibrary', $type);
	}

	/**
	 * Get the aggregated pages of anime or manga history
	 *
	 * @return mixed[]
	 */
	protected function getHistoryList(): array
	{
		return $this->requestBuilder->runQuery('GetUserHistory', [
			'slug' => $this->getUsername(),
		]);
	}

	/**
	 * Get all the raw data for the current list, chunking by status
	 *
	 * @param string $queryName - The GraphQL query
	 * @param string $type - Media type (anime, manga)
	 * @return array
	 */
	protected function getZippedListPerStatus(string $queryName, string $type): array
	{
		$statusPages = [];

		// Although I can fetch the whole list without segregating by status,
		// this way is much faster...
		foreach (MediaStatus::getConstList() as $status)
		{
			$statusPages[] = $this->getZippedList($queryName, $type, $status);
		}

		return array_merge(...$statusPages);
	}

	/**
	 * Get all the raw data for the current list
	 *
	 * @param string $queryName - The GraphQL query
	 * @param string $type - Media type (anime, manga)
	 * @param string $status - Media 'consumption' status
	 * @return array
	 */
	protected function getZippedList(string $queryName, string $type, string $status): array
	{
		$pages = [];

		foreach ($this->getListPages($queryName, $type, $status) as $page)
		{
			$pages[] = $page;
		}

		return array_merge(...$pages);
	}

	/**
	 * Get the raw anime/manga list from GraphQL
	 *
	 * @return mixed[]
	 */
	protected function getList(string $type, string $status = ''): array
	{
		return $this->getZippedList('GetLibrary', $type, $status);
	}

	/**
	 * A generator returning the relevant snippet for each 'page' of
	 * a media list request
	 *
	 * @param string $queryName - The GraphQL query
	 * @param string $type - Media type (anime, manga)
	 * @param string $status - Media 'consumption' status
	 * @return iterable
	 */
	private function getListPages(string $queryName, string $type, string $status): iterable
	{
		$cursor = '';
		$username = $this->getUsername();

		while (TRUE)
		{
			$vars = [
				'type' => strtoupper($type),
				'slug' => $username,
			];
			if ($status !== '')
			{
				$vars['status'] = strtoupper($status);
			}
			if ($cursor !== '')
			{
				$vars['after'] = $cursor;
			}

			$request = $this->requestBuilder->queryRequest($queryName, $vars);
			$response = getApiClient()->request($request);
			$json = $response->getBody()->buffer();

			$rawData = Json::decode($json);
			$data = $rawData['data']['findProfileBySlug']['library']['all'] ?? [];
			$page = $data['pageInfo'] ?? [];
			if (empty($data))
			{
				// Clear session, in case the error is an invalid token.
				$segment = $this->container->get('session')
					->getSegment(SESSION_SEGMENT);
				$segment->clear();

				// @TODO Proper Error logging
				dump($rawData);

				exit();
			}

			$cursor = $page['endCursor'];

			yield $data['nodes'];

			if ($page['hasNextPage'] === FALSE || $page === [])
			{
				break;
			}
		}
	}

	private function getListCount(string $type, string $status = ''): int
	{
		$args = [
			'type' => strtoupper($type),
			'slug' => $this->getUsername(),
		];
		if ($status !== '')
		{
			$args['status'] = strtoupper($status);
		}

		$res = $this->requestBuilder->runQuery('GetLibraryCount', $args);

		return $res['data']['findProfileBySlug']['library']['all']['totalCount'];
	}

	protected function getUserId(): string
	{
		static $userId = NULL;

		if ($userId === NULL)
		{
			$userId = $this->getUserIdByUsername($this->getUsername());
		}

		return $userId;
	}

	/**
	 * Get the kitsu username from config
	 */
	private function getUsername(): string
	{
		return $this->getContainer()
			->get('config')
			->get(['kitsu_username']);
	}
}
