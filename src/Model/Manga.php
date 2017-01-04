<?php declare(strict_types=1);
/**
 * Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     AnimeListClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2016  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Model;

use Aviat\AnimeClient\API\Kitsu\Enum\MangaReadingStatus;
use Aviat\AnimeClient\API\Kitsu\Transformer;
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Json;
use GuzzleHttp\Cookie\SetCookie;
use RuntimeException;

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

	public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        $this->kitsuModel = $container->get('kitsu-model');
    }

	/**
	 * Make an authenticated manga API call
	 *
	 * @param string $type - the HTTP verb
	 * @param string $url
	 * @param string|null $json
	 * @return array
	 */
	protected function _manga_api_call(string $type, string $url, $json = NULL): array
	{
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

		$config = [
			'cookies' => $cookieJar
		];

		if ( ! is_null($json))
		{
			$config['json'] = $json;
		}

		$result = $this->client->request(strtoupper($type), $url, $config);

		return [
			'statusCode' => $result->getStatusCode(),
			'body' => $result->getBody()
		];
	}


	/**
	 * Get a category out of the full list
	 *
	 * @param string $status
	 * @return array
	 */
	public function get_list($status)
	{
	    $data = $this->kitsuModel->getMangaList($status);
	    return $this->map_by_status($data)[$status];
		/*$data = $this->cache->get($this, '_get_list_from_api');
		return ($status !== 'All') ? $data[$status] : $data;*/
	}


	/**
	 * Get the details of a manga
	 *
	 * @param string $manga_id
	 * @return array
	 */
	public function get_manga($manga_id)
	{
		$raw = $this->_manga_api_call('get', "manga/{$manga_id}.json");
		return Json::decode($raw['body'], TRUE);
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

		$util = $this->container->get('util');

		foreach ($data as &$entry)
		{
			/*$entry['manga']['image'] = $util->get_cached_image(
				$entry['manga']['image'],
				$entry['manga']['slug'],
				'manga'
			);*/
			$key = $this->const_map[$entry['reading_status']];
			$output[$key][] = $entry;
		}

		foreach($output as &$val)
		{
			$this->sort_by_name($val, 'manga');
		}

		return $output;
	}

	/**
	 * Combine the two manga lists into one
	 * @param  array $raw_data
	 * @return array
	 */
	private function zipperLists($raw_data)
	{
		return (new Transformer\MangaListsZipper($raw_data))->transform();
	}
}
// End of MangaModel.php