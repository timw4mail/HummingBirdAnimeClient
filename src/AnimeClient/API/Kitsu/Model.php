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

use Amp\Http\Client\Request;
use Aviat\AnimeClient\API\{
	CacheTrait,
	JsonAPI,
	Kitsu as K,
	ParallelAPIRequest
};
use Aviat\AnimeClient\API\Kitsu\Transformer\{
	AnimeTransformer,
	AnimeListTransformer,
	LibraryEntryTransformer,
	MangaTransformer,
	MangaListTransformer
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

	protected const LIST_PAGE_SIZE = 75;

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
	 * @param string $id
	 * @return array
	 * @throws InvalidArgumentException
	 */
	public function getPerson(string $id): array
	{
		return $this->getCached("kitsu-person-{$id}", fn () => $this->requestBuilder->getRequest("people/{$id}", [
			'query' => [
				'filter' => [
					'id' => $id,
				],
				'fields' => [
					'characters' => 'canonicalName,slug,image',
					'characterVoices' => 'mediaCharacter',
					'anime' => 'canonicalTitle,abbreviatedTitles,titles,slug,posterImage',
					'manga' => 'canonicalTitle,abbreviatedTitles,titles,slug,posterImage',
					'mediaCharacters' => 'role,media,character',
					'mediaStaff' => 'role,media,person',
				],
				'include' => 'voices.mediaCharacter.media,voices.mediaCharacter.character,staff.media',
			],
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

		$raw = $this->requestBuilder->getRequest('mappings', $options);

		if ( ! array_key_exists('included', $raw))
		{
			return NULL;
		}

		return $raw['included'][0]['id'];
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
		$options = [
			'filter' => [
				'user_id' => $this->getUserId(),
				'kind' => $type,
			],
			'include' => "{$type},{$type}.mappings",
			// 'sort' => '-updated_at'
		];

		return $this->getRawSyncList($type, $options);
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
		return $this->requestBuilder->setUpRequest('GET', 'library-events', [
			'query' => [
				'filter' => [
					'kind' => 'progressed,updated',
					'userId' => $this->getUserId(),
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
		$options = [
			'query' => [
				'filter' => [
					'user_id' => $this->getUserId(),
					'kind' => $type,
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

		$response = $this->requestBuilder->getRequest('library-entries', $options);

		return $response['meta']['count'];
	}

	/**
	 * Get the full anime list
	 *
	 * @param string $type
	 * @param array $options
	 * @return array
	 * @throws InvalidArgumentException
	 * @throws Throwable
	 */
	private function getRawSyncList(string $type, array $options): array
	{
		$count = $this->getListCount($type);
		$size = static::LIST_PAGE_SIZE;
		$pages = ceil($count / $size);

		$requester = new ParallelAPIRequest();

		// Set up requests
		for ($i = 0; $i < $pages; $i++)
		{
			$offset = $i * $size;
			$requester->addRequest($this->getRawSyncListPage($type, $size, $offset, $options));
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
	 * Get the full anime list in paginated form
	 *
	 * @param string $type
	 * @param int $limit
	 * @param int $offset
	 * @param array $options
	 * @return Request
	 * @throws InvalidArgumentException
	 */
	private function getRawSyncListPage(string $type, int $limit, int $offset = 0, array $options = []): Request
	{
		$defaultOptions = [
			'filter' => [
				'user_id' => $this->getUserId(),
				'kind' => $type,
			],
			'page' => [
				'offset' => $offset,
				'limit' => $limit
			],
			'sort' => '-updated_at'
		];
		$options = array_merge($defaultOptions, $options);

		return $this->requestBuilder->setUpRequest('GET', 'library-entries', ['query' => $options]);
	}
}