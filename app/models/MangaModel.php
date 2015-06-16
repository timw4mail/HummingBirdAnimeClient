<?php
/**
 * Manga API Model
 */

/**
 * Model for handling requests dealing with the manga list
 */
class MangaModel extends BaseApiModel {

	/**
	 * @var string $base_url - The base url for api requests
	 */
	protected $base_url = "https://hummingbird.me";

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
	 * @param string $status
	 * @return array
	 */
	public function get_list($status)
	{
		$data = $this->_get_list($status);

		$this->sort_by_name($data);

		return $data;
	}

	/**
	 * Massage the list of manga entries into something more usable
	 *
	 * @param string $status
	 * @return array
	 */
	private function _get_list($status="all")
	{
		global $defaultHandler;

		$cache_file = _dir($this->config->data_cache_path, 'manga.json');

		$config = [
			'query' => [
				'user_id' => $this->config->hummingbird_username
			],
			'allow_redirects' => false
		];

		$response = $this->client->get($this->_url('/manga_library_entries'), $config);

		$defaultHandler->addDataTable('response', (array)$response);

		if ($response->getStatusCode() != 200)
		{
			if ( ! file_exists($cache_file))
			{
				throw new Exception($response->getEffectiveUrl());
			}
			else
			{
				$raw_data = json_decode(file_get_contents($cache_file), TRUE);
			}
		}
		else
		{
			// Reorganize data to be more usable
			$raw_data = $response->json();

			// Cache data in case of downtime
			file_put_contents($cache_file, json_encode($raw_data));
		}

		$data = [
			'Reading' => [],
			'Plan to Read' => [],
			'On Hold' => [],
			'Dropped' => [],
			'Completed' => [],
		];
		$manga_data = [];

		// Massage the two lists into one
		foreach($raw_data['manga'] as $manga)
		{
			$manga_data[$manga['id']] = $manga;
		}

		// Filter data by status
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

		return (array_key_exists($status, $data)) ? $data[$status] : $data;
	}

	/**
	 * Sort the manga entries by their title
	 *
	 * @param array $array
	 * @return void
	 */
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