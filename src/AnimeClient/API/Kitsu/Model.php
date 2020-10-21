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

use function Amp\Promise\wait;
use function Aviat\AnimeClient\getApiClient;

use Amp;
use Amp\Http\Client\Request;
use Aviat\AnimeClient\Kitsu as K;
use Aviat\AnimeClient\API\{
	CacheTrait,
	JsonAPI,
	ParallelAPIRequest
};
use Aviat\AnimeClient\API\Kitsu\Transformer\{
	AnimeTransformer,
	LibraryEntryTransformer,
	MangaTransformer,
};

use Aviat\Banker\Exception\InvalidArgumentException;
use Aviat\Ion\{Di\ContainerAware, Json};

use Throwable;

/**
 * Kitsu API Model
 */
final class Model {
	use CacheTrait;
	use ContainerAware;
	use RequestBuilderTrait;
	use AnimeTrait;
	use MangaTrait;
	use MutationTrait;

	protected const LIST_PAGE_SIZE = 100;

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

		$raw = $this->requestBuilder->getRequest($type, $options);
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
			foreach ($this->getRawSyncListPages(strtoupper($type), $status) as $page)
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
	protected function getRawHistoryList(): array
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
	public function getRawList(string $type, string $status = ''): array
	{
		$pages = [];

		foreach ($this->getRawListPages(strtoupper($type), strtoupper($status)) as $page)
		{
			$pages[] = $page;
		}

		return array_merge(...$pages);
	}

	protected function getRawListPages(string $type, string $status = ''): ?\Generator
	{
		$items = $this->getRawPages($type, $status);

		while (wait($items->advance()))
		{
			yield $items->getCurrent();
		}
	}

	protected function getRawPages(string $type, string $status = ''): Amp\Iterator
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

	protected function getRawSyncListPages(string $type, string $status): ?\Generator
	{
		$items = $this->getRawSyncPages($type, $status);

		while (wait($items->advance()))
		{
			yield $items->getCurrent();
		}
	}

	protected function getRawSyncPages(string $type, $status): Amp\Iterator {
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