<?php
/**
 * Anime API Model
 */

namespace Aviat\AnimeClient\Model;

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
	 * @return array
	 */
	public function update($data)
	{
		// @TODO use Hummingbird Auth class
		$data['auth_token'] = '';

		$response = $this->client->post("libraries/{$data['id']}", [
			'body' => $data
		]);

		return json_decode($result->getBody(), TRUE);
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
			$this->sort_by_name($status_list);
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
		$data = $this->_get_list_From_api($status);
		$this->sort_by_name($data);

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

		return $response->json();
	}

	/**
	 * Search for anime by name
	 *
	 * @param string $name
	 * @return array
	 */
	public function search($name)
	{
		$errorHandler = $this->container->get('error-handler');

		$config = [
			'query' => [
				'query' => $name
			]
		];

		$response = $this->get('search/anime', $config);
		$errorHandler->addDataTable('anime_search_response', (array)$response);

		if ($response->getStatusCode() != 200)
		{
			throw new RuntimeException($response->getEffectiveUrl());
		}

		return json_decode($response->getBody(), TRUE);
	}

	/**
	 * Retrieve data from the api
	 *
	 * @codeCoverageIgnore
	 * @param string $status
	 * @return array
	 */
	protected function _get_list_from_api($status = "all")
	{
		$config = [
			'allow_redirects' => FALSE
		];

		if ($status != "all")
		{
			$config['query']['status'] = $status;
		}

		$username = $this->config->get('hummingbird_username');
		$response = $this->get("users/{$username}/library", $config);
		$output = $this->_check_cache($status, $response);

		foreach ($output as &$row)
		{
			$row['anime']['image'] = $this->get_cached_image($row['anime']['image'], $row['anime']['slug'], 'anime');
		}

		return $output;
	}

	/**
	 * Handle caching of transformed api data
	 *
	 * @codeCoverageIgnore
	 * @param string $status
	 * @param \GuzzleHttp\Message\Response
	 * @return array
	 */
	protected function _check_cache($status, $response)
	{
		$cache_file = _dir($this->config->get('data_cache_path'), "anime-{$status}.json");
		$transformed_cache_file = _dir($this->config->get('data_cache_path'), "anime-{$status}-transformed.json");

		$cached = json_decode(file_get_contents($cache_file), TRUE);
		$api_data = json_decode($response->getBody(), TRUE);

		if ($api_data === $cached && file_exists($transformed_cache_file))
		{
			return json_decode(file_get_contents($transformed_cache_file), TRUE);
		}
		else
		{
			file_put_contents($cache_file, json_encode($api_data));
			$transformer = new AnimeListTransformer();
			$transformed = $transformer->transform_collection($api_data);
			file_put_contents($transformed_cache_file, json_encode($transformed));
			return $transformed;
		}
	}

	/**
	 * Sort the list by title
	 *
	 * @param array $array
	 * @return void
	 */
	protected function sort_by_name(&$array)
	{
		$sort = array();

		foreach ($array as $key => $item)
		{
			$sort[$key] = $item['anime']['title'];
		}

		array_multisort($sort, SORT_ASC, $array);
	}
}
// End of AnimeModel.php