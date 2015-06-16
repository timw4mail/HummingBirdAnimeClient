<?php
/**
 * Anime API Model
 */

/**
 * Model for handling requests dealing with the anime list
 */
class AnimeModel extends BaseApiModel {
	/**
	 * The base url for api requests
	 * @var string $base_url
	 */
	protected $base_url = "https://hummingbird.me/api/v1";

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Get the full set of anime lists
	 *
	 * @return array
	 */
	public function get_all_lists()
	{
		$output = [
			'Watching' => [],
			'Plan to Watch' => [],
			'On Hold' => [],
			'Dropped' => [],
			'Completed' => [],
		];

		$data = $this->_get_list();

		foreach($data as $datum)
		{
			switch($datum['status'])
			{
				case "completed":
					$output['Completed'][] = $datum;
				break;

				case "plan-to-watch":
					$output['Plan to Watch'][] = $datum;
				break;

				case "dropped":
					$output['Dropped'][] = $datum;
				break;

				case "on-hold":
					$output['On Hold'][] = $datum;
				break;

				case "currently-watching":
					$output['Watching'][] = $datum;
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
			'currently-watching' => 'Watching',
			'plan-to-watch' => 'Plan to Watch',
			'on-hold' => 'On Hold',
			'dropped' => 'Dropped',
			'completed' => 'Completed',
		];

		$data = $this->_get_list($status);
		$this->sort_by_name($data);

		$output = [];
		$output[$map[$status]] = $data;

		return $output;
	}

	/**
	 * Actually retreive the data from the api
	 *
	 * @param string $status - Status to filter by
	 * @return array
	 */
	private function _get_list($status="all")
	{
		global $defaultHandler;

		$cache_file = "{$this->config->data_cache_path}/anime-{$status}.json";

		$config = [
			'query' => [
				'username' => $this->config->hummingbird_username,
			],
			'allow_redirects' => false
		];

		if ($status != "all")
		{
			$config['query']['status'] = $status;
		}

		$response = $this->client->get($this->_url('/users/' . $this->config->hummingbird_username . '/library'), $config);

		$defaultHandler->addDataTable('anime_list_response', (array)$response);

		if ($response->getStatusCode() != 200)
		{
			if ( ! file_exists($cache_file))
			{
				throw new Exception($response->getEffectiveUrl());
			}
			else
			{
				$output = json_decode(file_get_contents($cache_file), TRUE);
			}
		}
		else
		{
			$output = $response->json();
			$output_json = json_encode($output);

			if (( ! file_exists($cache_file)) || file_get_contents($cache_file) !== $output_json)
			{
				// Cache the call in case of downtime
				file_put_contents($cache_file, json_encode($output));
			}
		}

		foreach($output as &$row)
		{
			$row['anime']['cover_image'] = $this->get_cached_image($row['anime']['cover_image'], $row['anime']['slug'], 'anime');
		}

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

		$response = $this->client->get($this->_url("/anime/{$anime_id}"), $config);

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
		global $defaultHandler;

		$config = [
			'query' => [
				'query' => $name
			]
		];

		$response = $this->client->get($this->_url('/search/anime'), $config);
		$defaultHandler->addDataTable('anime_search_response', (array)$response);

		if ($response->getStatusCode() != 200)
		{
			throw new Exception($response->getEffectiveUrl());
		}

		return $response->json();
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