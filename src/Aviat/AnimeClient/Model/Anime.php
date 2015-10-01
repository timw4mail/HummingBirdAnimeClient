<?php
/**
 * Anime API Model
 */

namespace Aviat\AnimeClient\Model;

use Aviat\AnimeClient\Model\API;
use Aviat\AnimeClient\Enum\Hummingbird\AnimeWatchingStatus;
use Aviat\AnimeClient\Transformer\Hummingbird\AnimeListTransformer;

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
	 * Update the selected anime
	 *
	 * @param array $data
	 * @return array
	 */
	public function update($data)
	{
		$data['auth_token'] = $_SESSION['hummingbird_anime_token'];

		$result = $this->client->post("libraries/{$data['id']}", [
			'body' => $data
		]);

		return $result->json();
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

		$data = $this->_get_list();

		foreach($data as $datum)
		{
			switch($datum['status'])
			{
				case AnimeWatchingStatus::COMPLETED:
					$output[self::COMPLETED][] = $datum;
				break;

				case AnimeWatchingStatus::PLAN_TO_WATCH:
					$output[self::PLAN_TO_WATCH][] = $datum;
				break;

				case AnimeWatchingStatus::DROPPED:
					$output[self::DROPPED][] = $datum;
				break;

				case AnimeWatchingStatus::ON_HOLD:
					$output[self::ON_HOLD][] = $datum;
				break;

				case AnimeWatchingStatus::WATCHING:
					$output[self::WATCHING][] = $datum;
				break;
			}
		}

		// Sort anime by name
		foreach($output as &$status_list)
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
		$map = [
			AnimeWatchingStatus::WATCHING => self::WATCHING,
			AnimeWatchingStatus::PLAN_TO_WATCH => self::PLAN_TO_WATCH,
			AnimeWatchingStatus::ON_HOLD => self::ON_HOLD,
			AnimeWatchingStatus::DROPPED => self::DROPPED,
			AnimeWatchingStatus::COMPLETED => self::COMPLETED,
		];

		$data = $this->_get_list_From_api($status);
		$this->sort_by_name($data);

		$output = [];
		$output[$map[$status]] = $data;

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

		$response = $this->client->get('search/anime', $config);
		$errorHandler->addDataTable('anime_search_response', (array)$response);

		if ($response->getStatusCode() != 200)
		{
			throw new RuntimeException($response->getEffectiveUrl());
		}

		return $response->json();
	}

	/**
	 * Retrieve data from the api
	 *
	 * @param string $status
	 * @return array
	 */
	private function _get_list_from_api($status="all")
	{
		$config = [
			'allow_redirects' => FALSE
		];

		if ($status != "all")
		{
			$config['query']['status'] = $status;
		}

		$response = $this->client->get("users/{$this->config->hummingbird_username}/library", $config);
		$output = $this->_check_cache($status, $response);

		foreach($output as &$row)
		{
			$row['anime']['image'] = $this->get_cached_image($row['anime']['image'], $row['anime']['slug'], 'anime');
		}

		return $output;
	}

	/**
	 * Handle caching of transformed api data
	 *
	 * @param string $status
	 * @param \GuzzleHttp\Message\Response
	 * @return array
	 */
	private function _check_cache($status, $response)
	{
		$cache_file = "{$this->config->data_cache_path}/anime-{$status}.json";
		$transformed_cache_file = "{$this->config->data_cache_path}/anime-{$status}-transformed.json";

		$cached = json_decode(file_get_contents($cache_file), TRUE);
		$api = $response->json();

		if ($api !== $cached)
		{
			file_put_contents($cache_file, json_encode($api));
			$transformer = new AnimeListTransformer();
			$transformed = $transformer->transform_collection($api);
			file_put_contents($transformed_cache_file, json_encode($transformed));
			return $transformed;
		}
		else
		{
			return json_decode(file_get_contents($transformed_cache_file),TRUE);
		}
	}

	/**
	 * Sort the list by title
	 *
	 * @param array $array
	 * @return void
	 */
	private function sort_by_name(&$array)
	{
		$sort = array();

		foreach($array as $key => $item)
		{
			$sort[$key] = $item['anime']['title'];
		}

		array_multisort($sort, SORT_ASC, $array);
	}
}
// End of AnimeModel.php