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

use Aviat\AnimeClient\API\Kitsu\Transformer\AnimeListTransformer;
use Aviat\Ion\Json;

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

	public function __construct()
	{
		// Set up Guzzle trait
		$this->init();

		$this->animeListTransformer = new AnimeListTransformer();
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
		$response = $this->post('https://kitsu.io/api/oauth/token', [
			'body' => http_build_query([
				'grant_type' => 'password',
				'username' => $username,
				'password' => $password,
				'client_id' => self::CLIENT_ID,
				'client_secret' => self::CLIENT_SECRET
			])
		]);

		$info = JSON::decode($response->getBody());

		if (array_key_exists('access_token', $info)) {
			// @TODO save token
			return true;
		}

		return false;
	}

	public function getAnimeMedia($entryId): array {
		$response = $this->get("library-entries/{$entryId}/media", [
			'headers' => [
				'client_id' => self::CLIENT_ID,
				'client_secret' => self::CLIENT_SECRET
			]
		]);

		return JSON::decode($response->getBody(), TRUE);
	}

	public function getAnimeList(): array {
		$response = $this->get('library-entries', [
			'headers' => [
				'client_id' => self::CLIENT_ID,
				'client_secret' => self::CLIENT_SECRET
			],
			'query' => [
				'filter' => [
					'user_id' => 2644,
					'media_type' => 'Anime'
				],
				'include' => 'media'
			]
		]);

		$data = JSON::decode($response->getBody(), TRUE);

		foreach($data['data'] as &$item)
		{
			$item['anime'] = $this->getAnimeMedia($item['id'])['data']['attributes'];
		}

		$transformed = $this->animeListTransformer->transformCollection($data['data']);

		return $transformed;
	}
}