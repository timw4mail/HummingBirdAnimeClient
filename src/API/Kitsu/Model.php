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
 */

namespace Aviat\AnimeClient\API\Kitsu;

use Aviat\AnimeClient\API\CacheTrait;
use Aviat\AnimeClient\API\JsonAPI;
use Aviat\AnimeClient\API\Kitsu as K;
use Aviat\AnimeClient\API\Kitsu\Transformer\{
	AnimeTransformer, 
	AnimeListTransformer, 
	MangaTransformer, 
	MangaListTransformer
};
use Aviat\Ion\Di\ContainerAware;
use Aviat\Ion\Json;
use GuzzleHttp\Exception\ClientException;

/**
 * Kitsu API Model
 */
class Model {
	use CacheTrait;
	use ContainerAware;
	use KitsuTrait;

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
	 * Constructor.
	 */
	public function __construct(ListItem $listItem)
	{
		// Set up Guzzle trait
		$this->init();

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

		return false;
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

			foreach($data['data'] as $i => &$item)
			{
				$item['manga'] = $data['included'][$i];
			}

			$transformed = $this->mangaListTransformer->transformCollection($data['data']);

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
	 * @return bool
	 */
	public function createListItem(array $data): bool
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
	 * @return array
	 */
	public function updateListItem(array $data)
	{
		try
		{
			$response = $this->listItem->update($data['id'], $data['data']);
			return [
				'statusCode' => $response->getStatusCode(),
				'body' => $response->getBody(),
			];
		}
		catch(ClientException $e)
		{
			return [
				'statusCode' => $e->getResponse()->getStatusCode(),
				'body' => Json::decode((string)$e->getResponse()->getBody())
			];
		}
	}

	/**
	 * Remove a list item
	 *
	 * @param string $id - The id of the list item to remove
	 * @return bool
	 */
	public function deleteListItem(string $id): bool
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