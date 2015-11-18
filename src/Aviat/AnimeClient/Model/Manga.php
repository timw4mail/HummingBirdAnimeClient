<?php
/**
 * Hummingbird Anime Client
 *
 * An API client for Hummingbird to manage anime and manga watch lists
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren
 * @copyright   Copyright (c) 2015
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 * @license     MIT
 */
namespace Aviat\AnimeClient\Model;

use Aviat\AnimeClient\Model\API;
use Aviat\AnimeClient\Hummingbird\Transformer;
use Aviat\AnimeClient\Hummingbird\Enum\MangaReadingStatus;

use GuzzleHttp\Cookie\Cookiejar;
use GuzzleHttp\Cookie\SetCookie;

/**
 * Model for handling requests dealing with the manga list
 */
class Manga extends API {

	const READING = 'Reading';
	const PLAN_TO_READ = 'Plan to Read';
	const DROPPED = 'Dropped';
	const ON_HOLD = 'On Hold';
	const COMPLETED = 'Completed';

	/**
	 * Map API constants to display constants
	 * @var array
	 */
	protected $const_map = [
		MangaReadingStatus::READING => self::READING,
		MangaReadingStatus::PLAN_TO_READ => self::PLAN_TO_READ,
		MangaReadingStatus::ON_HOLD => self::ON_HOLD,
		MangaReadingStatus::DROPPED => self::DROPPED,
		MangaReadingStatus::COMPLETED => self::COMPLETED
	];

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

		$token = $this->container->get('auth')
			->get_auth_token();

		// Set the token cookie, with the authentication token
		// from the auth class.
		$cookieJar = $this->cookieJar;
		$cookie_data = new SetCookie([
			'Name' => 'token',
			'Value' => $token,
			'Domain' => 'hummingbird.me'
		]);
		$cookieJar->setCookie($cookie_data);

		$result = $this->put("manga_library_entries/{$id}", [
			'cookies' => $cookieJar,
			'json' => ['manga_library_entry' => $data]
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
		$data = $this->_get_list_from_api();

		foreach ($data as &$val)
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
		$data = $this->_get_list_from_api($status);
		$this->sort_by_name($data);

		return $data;
	}

	/**
	 * Retrieve the list from the hummingbird api
	 *
	 * @param  string $status
	 * @return array
	 */
	private function _get_list_from_api($status = "All")
	{

		$config = [
			'query' => [
				'user_id' => $this->config->get('hummingbird_username')
			],
			'allow_redirects' => FALSE
		];

		$response = $this->get('manga_library_entries', $config);
		$data = $this->_check_cache($response);
		$output = $this->map_by_status($data);

		return (array_key_exists($status, $output)) ? $output[$status] : $output;
	}

	/**
	 * Check the status of the cache and return the appropriate response
	 *
	 * @param \GuzzleHttp\Message\Response $response
	 * @codeCoverageIgnore
	 * @return array
	 */
	protected function _check_cache($response)
	{
		// Bail out early if there isn't any manga data
		$api_data = json_decode($response->getBody(), TRUE);
		if ( ! array_key_exists('manga', $api_data))
		{
			return [];
		}

		$cache_file = _dir($this->config->get('data_cache_path'), 'manga.json');
		$transformed_cache_file = _dir($this->config->get('data_cache_path'), 'manga-transformed.json');


		$cached_data = file_exists($cache_file)
			? json_decode(file_get_contents($cache_file), TRUE)
			: [];

		if ($cached_data === $api_data && file_exists($transformed_cache_file))
		{
			return json_decode(file_get_contents($transformed_cache_file), TRUE);
		}
		else
		{
			file_put_contents($cache_file, json_encode($api_data));

			$zippered_data = $this->zipper_lists($api_data);
			$transformer = new Transformer\MangaListTransformer();
			$transformed_data = $transformer->transform_collection($zippered_data);
			file_put_contents($transformed_cache_file, json_encode($transformed_data));
			return $transformed_data;
		}
	}

	/**
	 * Map transformed anime data to be organized by reading status
	 *
	 * @param array $data
	 * @return array
	 */
	private function map_by_status($data)
	{
		$output = [
			self::READING => [],
			self::PLAN_TO_READ => [],
			self::ON_HOLD => [],
			self::DROPPED => [],
			self::COMPLETED => [],
		];

		foreach ($data as &$entry)
		{
			$entry['manga']['image'] = $this->get_cached_image($entry['manga']['image'], $entry['manga']['slug'], 'manga');
			$key = $this->const_map[$entry['reading_status']];
			$output[$key][] = $entry;
		}

		return $output;
	}

	/**
	 * Combine the two manga lists into one
	 * @param  array $raw_data
	 * @return array
	 */
	private function zipper_lists($raw_data)
	{
		return (new Transformer\MangaListsZipper($raw_data))->transform();
	}

	/**
	 * Sort the manga entries by their title
	 *
	 * @codeCoverageIgnore
	 * @param array $array
	 * @return void
	 */
	private function sort_by_name(&$array)
	{
		$sort = array();

		foreach ($array as $key => $item)
		{
			$sort[$key] = $item['manga']['title'];
		}

		array_multisort($sort, SORT_ASC, $array);
	}
}
// End of MangaModel.php