<?php
/**
 * Manga API Model
 */
namespace Aviat\AnimeClient\Model;

use Aviat\AnimeClient\Model\API;
use Aviat\AnimeClient\Transformer\Hummingbird;

/**
 * Model for handling requests dealing with the manga list
 */
class Manga extends API {

	/**
	 * The base url for api requests
	 * @var string
	 */
	protected $base_url = "https://hummingbird.me/";

	/**
	 * Update the selected manga
	 *
	 * @param array $data
	 * @return array
	 */
	public function update($data)
	{
		$id = $data['id'];
		unset($data['id']);

		$result = $this->client->put("manga_library_entries/{$id}", [
			'cookies' => ['token' => $_SESSION['hummingbird_anime_token']],
			'json' => ['manga_library_entry' => $data]
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
		$errorHandler = $this->container->get('error-handler');

		$cache_file = _dir($this->config->data_cache_path, 'manga.json');

		$config = [
			'query' => [
				'user_id' => $this->config->hummingbird_username
			],
			'allow_redirects' => FALSE
		];

		$response = $this->client->get('manga_library_entries', $config);

		$errorHandler->addDataTable('response', (array)$response);

		if ($response->getStatusCode() != 200)
		{
			if ( ! file_exists($cache_file))
			{
				throw new DomainException($response->getEffectiveUrl());
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

			// Attempt to create the cache dir if it doesn't exist
			if ( ! is_dir($this->config->data_cache_path))
			{
				mkdir($this->config->data_cache_path);
			}

			// Cache data in case of downtime
			file_put_contents($cache_file, json_encode($raw_data));
		}

		// Bail out early if there isn't any manga data
		if ( ! array_key_exists('manga', $raw_data)) return [];

		$data = [
			'Reading' => [],
			'Plan to Read' => [],
			'On Hold' => [],
			'Dropped' => [],
			'Completed' => [],
		];

		// Massage the two lists into one
		$manga_data = $this->zipper_lists($raw_data);

		// Filter data by status
		foreach($manga_data as &$entry)
		{
			// Cache poster images
			$entry['manga']['poster_image'] = $this->get_cached_image($entry['manga']['poster_image'], $entry['manga']['id'], 'manga');

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
	 * Combine the two manga lists into one
	 * @param  array $raw_data
	 * @return array
	 */
	private function zipper_lists($raw_data)
	{
		$zipper = new Hummingbird\MangaListsZipper($raw_data);
		return $zipper->transform();
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
