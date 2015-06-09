<?php

/**
 * Model for handling requests dealing with the manga list
 */
class MangaModel extends BaseModel {

	protected $client;
	protected $cookieJar;
	protected $base_url = "https://hummingbird.me";

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
		$data = $this->_get_list();

		foreach ($data as $key => &$val)
		{
			$this->sort_by_name($val);
		}

		return $data;
	}

	/**
	 * Get a category out of the full list
	 *
	 * @param string $type
	 * @return array
	 */
	public function get_list($type)
	{
		$data = $this->_get_list($type);

		$this->sort_by_name($data);

		return $data;
	}

	private function _get_list($type="all")
	{
		global $defaultHandler;

		$config = [
			'query' => [
				'user_id' => 'timw4mail',
			],
			'allow_redirects' => false
		];

		$response = $this->client->get($this->_url('/manga_library_entries'), $config);

		$defaultHandler->addDataTable('response', (array)$response);

		if ($response->getStatusCode() != 200)
		{
			throw new Exception($response->getEffectiveUrl());
		}

		// Reorganize data to be more usable
		$raw_data = $response->json();

		$data = [
			'Reading' => [],
			'Plan to Read' => [],
			'On Hold' => [],
			'Dropped' => [],
			'Completed' => [],
		];
		$manga_data = [];

		foreach($raw_data['manga'] as $manga)
		{
			$manga_data[$manga['id']] = $manga;
		}

		foreach($raw_data['manga_library_entries'] as &$entry)
		{
			$entry['manga'] = $manga_data[$entry['manga_id']];

			// Cache poster images
			$entry['manga']['poster_image'] = $this->get_cached_image($entry['manga']['poster_image'], $entry['manga_id'], 'manga');

			switch($entry['status'])
			{
				case "Plan to Read":
					$data['Plan to Read'][] = $entry;
				break;

				case "Dropped":
					$data['Dropped'][] = $entry;
				break;

				case "On Hold":
					$data['On Hold'][] = $entry;
				break;

				case "Currently Reading":
					$data['Reading'][] = $entry;
				break;

				case "Completed":
				default:
					$data['Completed'][] = $entry;
				break;
			}
		}

		return (array_key_exists($type, $data)) ? $data[$type] : $data;
	}

	private function sort_by_name(&$array)
	{
		$sort = array();

		foreach($array as $key => $item)
		{
			$sort[$key] = $item['manga']['romaji_title'];
		}

		array_multisort($sort, SORT_ASC, $array);
	}
}
// End of MangaModel.php