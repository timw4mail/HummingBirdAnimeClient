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
use Aviat\Ion\Json;
use GuzzleHttp\Exception\ClientException;

/**
 * Kitsu API Model
 */
class KitsuModel {

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
			'body' => http_build_query([
				'grant_type' => 'password',
				'username' => $username,
				'password' => $password,
				'client_id' => self::CLIENT_ID,
				'client_secret' => self::CLIENT_SECRET
			])
		]);

		if (array_key_exists('access_token', $data)) {
			// @TODO save token
			return true;
		}

		return false;
	}

	public function getAnime(string $animeId): array
	{
		$baseData = $this->getRawAnimeData($animeId);
		return $this->animeTransformer->transform($baseData);
	}

	public function getManga(string $mangaId): array
	{
		$baseData = $this->getRawMediaData('manga', $mangaId);
		return $this->mangaTransformer->transform($baseData);
	}

	public function getRawAnimeData($animeId): array
	{
		return $this->getRawMediaData('anime', $animeId);
	}

	public function getAnimeList($status): array
	{
		$options = [
			'query' => [
				'filter' => [
					'user_id' => 2644,
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
					'user_id' => 2644,
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

	private function getGenres(string $type, string $id): array
	{
		$data = $this->getRequest("{$type}/{$id}/genres");
		$rawGenres = array_pluck($data['data'], 'attributes');
		$genres = array_pluck($rawGenres, 'name');

		return $genres;
	}

	private function getRawMediaData(string $type, string $id): array
	{
		$data = $this->getRequest("{$type}/{$id}");
		$baseData = $data['data']['attributes'];
		$baseData['genres'] = $this->getGenres($type, $id);

		return $baseData;
	}
}