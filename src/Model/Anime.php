<?php declare(strict_types=1);
/**
 * Hummingbird Anime Client
 *
 * An API client for Hummingbird to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2016  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     3.1
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Model;

use Aviat\Ion\Json;
use Aviat\AnimeClient\Hummingbird\Enum\AnimeWatchingStatus;
use Aviat\AnimeClient\Hummingbird\Transformer\AnimeListTransformer;

/**
 * Model for handling requests dealing with the anime list
 */
class Anime extends API {

	// Display constants
	const WATCHING = 'Watching';
	const PLAN_TO_WATCH = 'Plan to Watch';
	const DROPPED = 'Dropped';
	const ON_HOLD = 'On Hold';
	const COMPLETED = 'Completed';

	/**
	 * The base url for api requests
	 * @var string $base_url
	 */
	protected $base_url = "https://hummingbird.me/api/v1/";

	/**
	 * Map of API status constants to display constants
	 * @var array
	 */
	protected $const_map = [
		AnimeWatchingStatus::WATCHING => self::WATCHING,
		AnimeWatchingStatus::PLAN_TO_WATCH => self::PLAN_TO_WATCH,
		AnimeWatchingStatus::ON_HOLD => self::ON_HOLD,
		AnimeWatchingStatus::DROPPED => self::DROPPED,
		AnimeWatchingStatus::COMPLETED => self::COMPLETED,
	];

	/**
	 * Update the selected anime
	 *
	 * @param array $data
	 * @return array|false
	 */
	public function update($data)
	{
		$auth = $this->container->get('auth');
		if ( ! $auth->is_authenticated() OR ! array_key_exists('id', $data))
		{
			return FALSE;
		}

		$id = $data['id'];
		$data['auth_token'] = $auth->get_auth_token();

		$response = $this->client->post("libraries/{$id}", [
			'form_params' => $data
		]);

		return [
			'statusCode' => $response->getStatusCode(),
			'body' => Json::decode($response->getBody(), TRUE)
		];
	}

	/**
	 * Remove an anime from a list
	 *
	 * @param  array $data
	 * @return array|false
	 */
	public function delete($data)
	{
		$auth = $this->container->get('auth');
		if ( ! $auth->is_authenticated() OR ! array_key_exists('id', $data))
		{
			return FALSE;
		}

		$id = $data['id'];
		$data['auth_token'] = $auth->get_auth_token();

		$response = $this->client->post("libraries/{$id}/remove", [
			'form_params' => $data
		]);

		return [
			'statusCode' => $response->getStatusCode(),
			'body' => Json::decode($response->getBody(), TRUE)
		];
	}

	/**
	 * Get the full set of anime lists
	 *
	 * @return array
	 */
	public function get_all_lists()
	{
		$output = [
			self::WATCHING => [],
			self::PLAN_TO_WATCH => [],
			self::ON_HOLD => [],
			self::DROPPED => [],
			self::COMPLETED => [],
		];

		$data = $this->_get_list_from_api();

		foreach ($data as $datum)
		{
			$output[$this->const_map[$datum['watching_status']]][] = $datum;
		}

		// Sort anime by name
		foreach ($output as &$status_list)
		{
			$this->sort_by_name($status_list, 'anime');
		}

		return $output;
	}

	/**
	 * Get a category out of the full list
	 *
	 * @param string $status
	 * @return array
	 */
	public function get_list($status)
	{
		$data = $this->_get_list_from_api($status);
		$this->sort_by_name($data, 'anime');

		$output = [];
		$output[$this->const_map[$status]] = $data;

		return $output;
	}

	/**
	 * Get information about an anime from its id
	 *
	 * @param string $anime_id
	 * @return array
	 */
	public function get_anime($anime_id)
	{
		$config = [
			'query' => [
				'id' => $anime_id
			]
		];

		$response = $this->client->get("anime/{$anime_id}", $config);

		return Json::decode($response->getBody(), TRUE);
	}

	/**
	 * Search for anime by name
	 *
	 * @param string $name
	 * @return array
	 * @throws \RuntimeException
	 */
	public function search($name)
	{
		$logger = $this->container->getLogger('default');

		$config = [
			'query' => [
				'query' => $name
			]
		];

		$response = $this->get('search/anime', $config);

		if ((int) $response->getStatusCode() !== 200)
		{
			$logger->warning("Non 200 response for search api call");
			$logger->warning($response->getBody());

			throw new \RuntimeException($response->getEffectiveUrl());
		}

		return Json::decode($response->getBody(), TRUE);
	}

	/**
	 * Retrieve data from the api
	 *
	 * @codeCoverageIgnore
	 * @param string $status
	 * @return array
	 */
	protected function _get_list_from_api(string $status = "all"): array
	{
		$config = [
			'allow_redirects' => FALSE
		];

		if ($status !== 'all')
		{
			$config['query']['status'] = $status;
		}

		$username = $this->config->get('hummingbird_username');
		$auth = $this->container->get('auth');
		if ($auth->is_authenticated())
		{
			$config['query']['auth_token'] = $auth->get_auth_token();
		}

		$response = $this->get("users/{$username}/library", $config);
		$output = $this->transform($status, $response);

		$util = $this->container->get('util');
		foreach ($output as &$row)
		{
			$row['anime']['image'] = $util->get_cached_image($row['anime']['image'], $row['anime']['slug'], 'anime');
		}

		return $output;
	}

	/**
	 * Get the full list from the api
	 *
	 * @return array
	 */
	public function get_raw_list()
	{
		$config = [
			'allow_redirects' => FALSE
		];

		$username = $this->config->get('hummingbird_username');

		$response = $this->get("users/{$username}/library", $config);
		return Json::decode($response->getBody(), TRUE);
	}

	/**
	 * Handle transforming of api data
	 *
	 * @param string $status
	 * @param \GuzzleHttp\Message\Response $response
	 * @return array
	 */
	protected function transform($status, $response)
	{
		$api_data = Json::decode($response->getBody(), TRUE);
		$transformer = new AnimeListTransformer();
		$transformed = $transformer->transformCollection($api_data);
		return $transformed;
	}
}
// End of AnimeModel.php