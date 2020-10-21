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
 * @version     5.1
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu;

use Amp;
use Aviat\AnimeClient\API\{
	CacheTrait,
	Enum\AnimeWatchingStatus\Kitsu as KitsuWatchingStatus,
	Enum\MangaReadingStatus\Kitsu as KitsuReadingStatus,
	Mapping\AnimeWatchingStatus,
	Mapping\MangaReadingStatus
};
use Aviat\AnimeClient\API\Kitsu\Transformer\{
	AnimeHistoryTransformer,
	AnimeListTransformer,
	AnimeTransformer,
	LibraryEntryTransformer,
	MangaHistoryTransformer,
	MangaListTransformer,
	MangaTransformer
};
use Aviat\AnimeClient\Enum\ListType;
use Aviat\AnimeClient\Kitsu as K;
use Aviat\AnimeClient\Types\Anime;
use Aviat\AnimeClient\Types\MangaPage;
use Aviat\Banker\Exception\InvalidArgumentException;
use Aviat\Ion\{
	Di\ContainerAware,
	Json
};
use Throwable;
use function Amp\Promise\wait;
use function Aviat\AnimeClient\getApiClient;

/**
 * Kitsu API Model
 */
final class Model {
	use CacheTrait;
	use ContainerAware;
	use RequestBuilderTrait;
	use MutationTrait;

	protected const LIST_PAGE_SIZE = 100;

	/**
	 * @var AnimeTransformer
	 */
	protected AnimeTransformer $animeTransformer;

	/**
	 * @var MangaTransformer
	 */
	protected MangaTransformer $mangaTransformer;

	/**
	 * @var ListItem
	 */
	protected ListItem $listItem;

	/**
	 * Constructor
	 *
	 * @param ListItem $listItem
	 */
	public function __construct(ListItem $listItem)
	{
		$this->animeTransformer = new AnimeTransformer();
		$this->mangaTransformer = new MangaTransformer();

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
		$response = $this->requestBuilder->getResponse('POST', K::AUTH_URL, [
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
			dump([
				'method' => __CLASS__ . '\\' . __METHOD__,
				'error' => $data['error'],
				'response' => $response,
			]);
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
		$response = $this->requestBuilder->getResponse('POST', K::AUTH_URL, [
			'headers' => [
				'accept' => NULL,
				'Content-type' => 'application/x-www-form-urlencoded',
				'Accept-encoding' => '*'
			],
			'form_params' => [
				'grant_type' => 'refresh_token',
				'refresh_token' => $token
			]
		]);
		$data = Json::decode(wait($response->getBody()->buffer()));

		if (array_key_exists('error', $data))
		{
			dump([
				'method' => __CLASS__ . '\\' . __METHOD__,
				'error' => $data['error'],
				'response' => $response,
			]);
			die();
		}

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
	 * @throws InvalidArgumentException
	 * @throws Throwable
	 */
	public function getUserIdByUsername(string $username = NULL): string
	{
		if ($username === NULL)
		{
			$username = $this->getUsername();
		}

		return $this->getCached(K::AUTH_USER_ID_KEY, function(string $username) {
			$data = $this->requestBuilder->getRequest('users', [
				'query' => [
					'filter' => [
						'name' => $username
					]
				]
			]);

			return $data['data'][0]['id'] ?? NULL;
		}, [$username]);
	}

	/**
	 * Get information about a character
	 *
	 * @param string $slug
	 * @return array
	 */
	public function getCharacter(string $slug): array
	{
		return $this->requestBuilder->runQuery('CharacterDetails', [
			'slug' => $slug
		]);
	}

	/**
	 * Get information about a person
	 *
	 * @param string $slug
	 * @return array
	 * @throws InvalidArgumentException
	 */
	public function getPerson(string $slug): array
	{
		return $this->getCached("kitsu-person-{$slug}", fn () => $this->requestBuilder->runQuery('PersonDetails', [
			'slug' => $slug
		]));
	}

	/**
	 * Get profile information for the configured user
	 *
	 * @param string $username
	 * @return array
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
	 *
	 * @param string $slug
	 * @return Anime
	 */
	public function getAnime(string $slug): Anime
	{
		$baseData = $this->requestBuilder->runQuery('AnimeDetails', [
			'slug' => $slug
		]);

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
		$baseData = $this->requestBuilder->runQuery('AnimeDetailsById', [
			'id' => $animeId,
		]);
		return $this->animeTransformer->transform($baseData);
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
	 * @return array
	 * @throws InvalidArgumentException
	 */
	public function getAnimeList(string $status): array
	{
		$key = "kitsu-anime-list-{$status}";

		$list = $this->cache->get($key, NULL);

		if ($list === NULL)
		{
			$data = $this->getList(ListType::ANIME, $status) ?? [];

			// Bail out on no data
			if (empty($data))
			{
				return [];
			}

			$transformer = new AnimeListTransformer();
			$transformed = $transformer->transformCollection($data);
			$keyed = [];

			foreach($transformed as $item)
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
	 * @return int
	 * @throws InvalidArgumentException
	 */
	public function getAnimeListCount(string $status = '') : int
	{
		return $this->getListCount(ListType::ANIME, $status);
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
		$baseData = $this->requestBuilder->runQuery('MangaDetails', [
			'slug' => $slug
		]);

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
		$baseData = $this->requestBuilder->runQuery('MangaDetailsById', [
			'id' => $mangaId,
		]);
		return $this->mangaTransformer->transform($baseData);
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
	 * @return array
	 * @throws InvalidArgumentException
	 */
	public function getMangaList(string $status): array
	{
		$key = "kitsu-manga-list-{$status}";

		$list = $this->cache->get($key, NULL);

		if ($list === NULL)
		{
			$data = $this->getList(ListType::MANGA, $status) ?? [];

			// Bail out on no data
			if (empty($data))
			{
				return [];
			}

			$transformer = new MangaListTransformer();
			$transformed = $transformer->transformCollection($data);
			$keyed = [];

			foreach($transformed as $item)
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
	 * @return int
	 * @throws InvalidArgumentException
	 */
	public function getMangaListCount(string $status = '') : int
	{
		return $this->getListCount(ListType::MANGA, $status);
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

	// ------------------------------------------------------------------------
	// Base methods
	// ------------------------------------------------------------------------

	/**
	 * Search for an anime or manga
	 *
	 * @param string $type - 'anime' or 'manga'
	 * @param string $query - name of the item to search for
	 * @return array
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
				'canonicalTitle' => $item['titles']['canonical'],
				'titles' => array_values(K::getTitles($item['titles'])),
			];

			// Search for MAL mapping
			if (is_array($item['mappings']['nodes']))
			{
				foreach($item['mappings']['nodes'] as $mapping)
				{
					if ($mapping['externalSite'] === "MYANIMELIST_" . strtoupper($type))
					{
						$searchItem['mal_id'] = $mapping['externalId'];
						break;
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
	 * @param string $malId
	 * @param string $type "anime" or "manga"
	 * @return string|NULL
	 */
	public function getKitsuIdFromMALId(string $malId, string $type='anime'): ?string
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
	 * @return mixed
	 */
	public function getListItem(string $listId)
	{
		$baseData = $this->listItem->get($listId);
		if ( ! isset($baseData['data']['findLibraryEntryById']))
		{
			return [];
		}

		return (new LibraryEntryTransformer())->transform($baseData['data']['findLibraryEntryById']);
	}

	public function getThumbList(string $type): array
	{
		$statuses = [
			'CURRENT',
			'PLANNED',
			'ON_HOLD',
			'DROPPED',
			'COMPLETED',
		];

		$pages = [];

		// Although I can fetch the whole list without segregating by status,
		// this way is much faster...
		foreach ($statuses as $status)
		{
			foreach ($this->getPages([$this, 'getThumbListPages'], strtoupper($type), $status) as $page)
			{
				$pages[] = $page;
			}
		}

		return array_merge(...$pages);
	}

	/**
	 * Get the data to sync Kitsu anime/manga list with another API
	 *
	 * @param string $type
	 * @return array
	 * @throws InvalidArgumentException
	 * @throws Throwable
	 */
	public function getSyncList(string $type): array
	{
		$statuses = [
			'CURRENT',
			'PLANNED',
			'ON_HOLD',
			'DROPPED',
			'COMPLETED',
		];

		$pages = [];

		// Although I can fetch the whole list without segregating by status,
		// this way is much faster...
		foreach ($statuses as $status)
		{
			foreach ($this->getPages([$this, 'getSyncPages'], strtoupper($type), $status) as $page)
			{
				$pages[] = $page;
			}
		}

		return array_merge(...$pages);
	}

	/**
	 * Get the aggregated pages of anime or manga history
	 *
	 * @return array
	 */
	protected function getHistoryList(): array
	{
		return $this->requestBuilder->runQuery('GetUserHistory', [
			'slug' => $this->getUsername(),
		]);
	}

	/**
	 * Get the raw anime/manga list from GraphQL
	 *
	 * @param string $type
	 * @param string $status
	 * @return array
	 */
	protected function getList(string $type, string $status = ''): array
	{
		$pages = [];

		foreach ($this->getPages([$this, 'getListPages'], strtoupper($type), strtoupper($status)) as $page)
		{
			$pages[] = $page;
		}

		return array_merge(...$pages);
	}

	private function getListPages(string $type, string $status = ''): Amp\Iterator
	{
		$cursor = '';
		$username = $this->getUsername();

		return new Amp\Producer(function (callable $emit) use ($type, $status, $cursor, $username) {
			while (TRUE)
			{
				$vars = [
					'type' => $type,
					'slug' => $username,
				];
				if ($status !== '')
				{
					$vars['status'] = $status;
				}
				if ($cursor !== '')
				{
					$vars['after'] = $cursor;
				}

				$request = $this->requestBuilder->queryRequest('GetLibrary', $vars);
				$response = yield getApiClient()->request($request);
				$json = yield $response->getBody()->buffer();

				$rawData = Json::decode($json);
				$data = $rawData['data']['findProfileBySlug']['library']['all'] ?? [];
				$page = $data['pageInfo'];
				if (empty($data))
				{
					dump($rawData);
					die();

					// @TODO Error logging
					break;
				}

				$cursor = $page['endCursor'];

				yield $emit($data['nodes']);

				if ($page['hasNextPage'] === FALSE)
				{
					break;
				}
			}
		});
	}

	private function getSyncPages(string $type, string $status): Amp\Iterator {
		$cursor = '';
		$username = $this->getUsername();

		return new Amp\Producer(function (callable $emit) use ($type, $status, $cursor, $username) {
			while (TRUE)
			{
				$vars = [
					'type' => $type,
					'slug' => $username,
					'status' => $status,
				];
				if ($cursor !== '')
				{
					$vars['after'] = $cursor;
				}

				$request = $this->requestBuilder->queryRequest('GetSyncLibrary', $vars);
				$response = yield getApiClient()->request($request);
				$json = yield $response->getBody()->buffer();

				$rawData = Json::decode($json);
				$data = $rawData['data']['findProfileBySlug']['library']['all'] ?? [];
				$page = $data['pageInfo'];
				if (empty($data))
				{
					dump($rawData);
					die();
				}

				$cursor = $page['endCursor'];

				yield $emit($data['nodes']);

				if ($page['hasNextPage'] === FALSE)
				{
					break;
				}
			}
		});
	}

	private function getThumbListPages(string $type, string $status): Amp\Iterator
	{
		$cursor = '';
		$username = $this->getUsername();

		return new Amp\Producer(function (callable $emit) use ($type, $status, $cursor, $username) {
			while (TRUE)
			{
				$vars = [
					'type' => $type,
					'slug' => $username,
					'status' => $status,
				];
				if ($cursor !== '')
				{
					$vars['after'] = $cursor;
				}

				$request = $this->requestBuilder->queryRequest('GetLibraryThumbs', $vars);
				$response = yield getApiClient()->request($request);
				$json = yield $response->getBody()->buffer();

				$rawData = Json::decode($json);
				$data = $rawData['data']['findProfileBySlug']['library']['all'] ?? [];
				$page = $data['pageInfo'];
				if (empty($data))
				{
					dump($rawData);
					die();
				}

				$cursor = $page['endCursor'];

				yield $emit($data['nodes']);

				if ($page['hasNextPage'] === FALSE)
				{
					break;
				}
			}
		});
	}

	private function getPages(callable $method, ...$args): ?\Generator
	{
		$items = $method(...$args);

		while (wait($items->advance()))
		{
			yield $items->getCurrent();
		}
	}

	private function getUserId(): string
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
	 *
	 * @return string
	 */
	private function getUsername(): string
	{
		return $this->getContainer()
			->get('config')
			->get(['kitsu_username']);
	}

	private function getListCount(string $type, string $status = ''): int
	{
		$args = [
			'type' => strtoupper($type),
			'slug' => $this->getUsername()
		];
		if ($status !== '')
		{
			$args['status'] = strtoupper($status);
		}

		$res = $this->requestBuilder->runQuery('GetLibraryCount', $args);

		return $res['data']['findProfileBySlug']['library']['all']['totalCount'];
	}
}