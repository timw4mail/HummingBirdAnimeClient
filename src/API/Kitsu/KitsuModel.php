<?php declare(strict_types=1);
/**
 * Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package	 AnimeListClient
 * @author	  Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2016  Timothy J. Warren
 * @license	 http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version	 4.0
 * @link		https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu;

use Aviat\AnimeClient\AnimeClient;
use Aviat\AnimeClient\API\Kitsu\Transformer\{
	AnimeTransformer, AnimeListTransformer, MangaTransformer, MangaListTransformer
};
use Aviat\Ion\Di\ContainerAware;

/**
 * Kitsu API Model
 */
class KitsuModel {
	use ContainerAware;
	use KitsuTrait;

	const CLIENT_ID = 'dd031b32d2f56c990b1425efe6c42ad847e7fe3ab46bf1299f05ecd856bdb7dd';
	const CLIENT_SECRET = '54d7307928f63414defd96399fc31ba847961ceaecef3a5fd93144e960c0e151';

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
	public function __construct()
	{
		// Set up Guzzle trait
		$this->init();

		$this->animeTransformer = new AnimeTransformer();
		$this->animeListTransformer = new AnimeListTransformer();
		$this->mangaTransformer = new MangaTransformer();
		$this->mangaListTransformer = new MangaListTransformer();
	}

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
		$data = $this->postRequest(AnimeClient::KITSU_AUTH_URL, [
			'form_params' => [
				'grant_type' => 'password',
				'username' => $username,
				'password' => $password
			]
		]);

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
	 * @param string $animeId
	 * @return array
	 */
	public function getManga(string $mangaId): array
	{
		$baseData = $this->getRawMediaData('manga', $mangaId);
		return $this->mangaTransformer->transform($baseData);
	}

	public function getListItem(string $listId): array
	{
		$baseData = $this->getRequest("library-entries/{$listId}", [
			'query' => [
				'include' => 'media'
			]
		]);

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

	public function getAnimeList($status): array
	{
		$options = [
			'query' => [
				'filter' => [
					'user_id' => $this->getUserIdByUsername($this->getUsername()),
					'media_type' => 'Anime',
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
			$item['anime'] = $data['included'][$i];
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
				]
			],
			'include' => 'media'
		];

		$data = $this->getRequest($type, $options);

		// @TODO implement search api call
		return $data;
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
				'include' => 'genres,mappings,streamingLinks',
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