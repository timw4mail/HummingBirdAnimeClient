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

use Aviat\AnimeClient\API\Kitsu as K;
use Aviat\AnimeClient\API\Kitsu\Transformer\{
	AnimeTransformer, AnimeListTransformer, MangaTransformer, MangaListTransformer
};
use Aviat\Ion\Di\ContainerAware;
use Aviat\Ion\Json;
use GuzzleHttp\Exception\ClientException;

/**
 * Kitsu API Model
 */
class KitsuModel {
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
	 * KitsuModel constructor.
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
	public function getUserIdByUsername(string $username)
	{
		$data = $this->getRequest('users', [
			'query' => [
				'filter' => [
					'name' => $username
				]
			]
		]);

		return $data['data'][0]['id'];
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
			return $data['access_token'];
		}

		return false;
	}

	/**
	 * Get information about a particular anime
	 *
	 * @param string $animeId
	 * @return array
	 */
	public function getAnime(string $animeId): array
	{
		$baseData = $this->getRawMediaData('anime', $animeId);
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

	public function getAnimeList($status): array
	{
		$options = [
			'query' => [
				'filter' => [
					'user_id' => $this->getUserIdByUsername($this->getUsername()),
					'media_type' => 'Anime',
					'status' => $status,
				],
				'include' => 'media,media.genres',
				'page' => [
					'offset' => 0,
					'limit' => 1000
				],
				'sort' => '-updated_at'
			]
		];

		$data = $this->getRequest('library-entries', $options);
		$included = K::organizeIncludes($data['included']);

		foreach($data['data'] as $i => &$item)
		{
			$item['anime'] = $included['anime'][$item['relationships']['media']['data']['id']];

			$animeGenres = $item['anime']['relationships']['genres'];

			foreach($animeGenres as $id)
			{
				$item['genres'][] = $included['genres'][$id]['name'];
			}
		}

		$transformed = $this->animeListTransformer->transformCollection($data['data']);

		return $transformed;
	}

	public function getMangaList($status): array
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
					'offset' => 0,
					'limit' => 200
				],
				'sort' => '-updated_at'
			]
		];

		$data = $this->getRequest('library-entries', $options);

		foreach($data['data'] as $i => &$item)
		{
			$item['manga'] = $data['included'][$i];
		}

		$transformed = $this->mangaListTransformer->transformCollection($data['data']);

		return $transformed;
	}

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

	public function createListItem(array $data): bool
	{
		$data['user_id'] = $this->getUserIdByUsername($this->getUsername());
		return $this->listItem->create($data);
	}

	public function getListItem(string $listId): array
	{
		$baseData = $this->listItem->get($listId);

		switch ($baseData['included'][0]['type'])
		{
			case 'anime':
				$baseData['data']['anime'] = $baseData['included'][0];
				return $this->animeListTransformer->transform($baseData['data']);

			case 'manga':
				$baseData['data']['manga'] = $baseData['included'][0];
				return $this->mangaListTransformer->transform($baseData['data']);

			default:
				return $baseData['data']['attributes'];
		}
	}

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
		$rawGenres = array_pluck($data['included'], 'attributes');
		$genres = array_pluck($rawGenres, 'name');
		$baseData['genres'] = $genres;
		$baseData['included'] = $data['included'];
		return $baseData;
	}
}