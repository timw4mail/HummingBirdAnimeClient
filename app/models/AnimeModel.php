<?php

/**
 * Model for handling requests dealing with the anime list
 */
class AnimeModel extends BaseModel {

	protected $client;
	protected $cookieJar;
	protected $base_url = "https://hummingbird.me/api/v1";

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
			'Completed' => [],
			'Plan to Watch' => [],
			'On Hold' => [],
			'Dropped' => []
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
	 * @param string $type
	 * @return array
	 */
	public function get_list($type)
	{
		$map = [
			'currently-watching' => 'Watching',
			'completed' => 'Completed',
			'plan-to-watch' => 'Plan to Watch',
			'on-hold' => 'On Hold',
			'dropped' => 'Dropped'
		];

		$data = $this->_get_list($type);
		$this->sort_by_name($data);

		$output = [];
		$output[$map[$type]] = $data;

		return $output;
	}

	private function _get_list($type="all")
	{
		global $defaultHandler;

		$config = [
			'query' => [
				'username' => 'timw4mail',
			],
			'allow_redirects' => false
		];

		if ($type != "all")
		{
			$config['query']['status'] = $type;
		}

		$response = $this->client->get($this->_url('/users/timw4mail/library'), $config);

		$defaultHandler->addDataTable('response', (array)$response);

		if ($response->getStatusCode() != 200)
		{
			throw new Exception($response->getEffectiveUrl());
		}

		return $response->json();
	}

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