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

use Aviat\AnimeClient\AnimeClient;
use Aviat\Ion\Di\{ContainerAware, ContainerInterface};
use Aviat\Ion\Model;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Psr\Http\Message\ResponseInterface;

/**
 * Base model for api interaction
 *
 * @method ResponseInterface get(string $uri, array $options);
 * @method ResponseInterface delete(string $uri, array $options);
 * @method ResponseInterface head(string $uri, array $options);
 * @method ResponseInterface options(string $uri, array $options);
 * @method ResponseInterface patch(string $uri, array $options);
 * @method ResponseInterface post(string $uri, array $options);
 * @method ResponseInterface put(string $uri, array $options);
 */
class API extends Model {

	use ContainerAware;

	/**
	 * Config manager
	 * @var ConfigInterface
	 */
	protected $config;

	/**
	 * Base url for making api requests
	 * @var string
	 */
	protected $base_url = '';

	/**
	 * The Guzzle http client object
	 * @var object
	 */
	protected $client;

	/**
	 * Cookie jar object for api requests
	 * @var object
	 */
	protected $cookieJar;

	/**
	 * Cache manager
	 * @var \Aviat\Ion\Cache\CacheInterface
	 */
	protected $cache;

    /**
     * Default settings for Guzzle
     * @var array
     */
	protected $connectionDefaults = [];

	/**
	 * Constructor
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
		$this->config = $container->get('config');
		$this->cache = $container->get('cache');
		$this->init();
	}

	/**
	 * Set up the class properties
	 *
	 * @return void
	 */
	protected function init()
	{
		$this->cookieJar = new CookieJar();
		$this->client = new Client([
			'base_uri' => $this->base_url,
			'cookies' => TRUE,
			'http_errors' => FALSE,
			'defaults' => array_merge([
				'cookies' => $this->cookieJar,
				'headers' => [
					'User-Agent' => "Tim's Anime Client/4.0",
					'Accept-Encoding' => 'application/vnd.api+json',
					'Content-Type' => 'application/vnd.api+json'
				],
				'timeout' => 25,
				'connect_timeout' => 25
			], $this->connectionDefaults)
		]);
	}

	/**
	 * Magic methods to call guzzle api client
	 *
	 * @param  string $method
	 * @param  array $args
	 * @return ResponseInterface|null
	 */
	public function __call($method, $args)
	{
		$valid_methods = [
			'get',
			'getAsync',
			'delete',
			'deleteAsync',
			'head',
			'headAsync',
			'options',
			'optionsAsync',
			'patch',
			'patchAsync',
			'post',
			'postAsync',
			'put',
			'putAsync'
		];

		if ( ! in_array($method, $valid_methods))
		{
			return NULL;
		}

		array_unshift($args, strtoupper($method));
		return call_user_func_array([$this->client, 'request'], $args);
	}

	/**
	 * Get the data for the specified library entry
	 *
	 * @param  string $id
	 * @param  string $status
	 * @return array
	 */
	public function get_library_item($id, $status)
	{
		$data = $this->_get_list_from_api($status);
		$index_array = array_column($data, 'id');

		$key = array_search($id, $index_array);

		return $key !== FALSE
			? $data[$key]
			: [];
	}

	/**
	 * Sort the manga entries by their title
	 *
	 * @codeCoverageIgnore
	 * @param array $array
	 * @param string $sort_key
	 * @return void
	 */
	protected function sort_by_name(array &$array, string $sort_key)
	{
		$sort = [];

		foreach ($array as $key => $item)
		{
			$sort[$key] = $item[$sort_key]['title'];
		}

		array_multisort($sort, SORT_ASC, $array);
	}

	/**
	 * Dummy function that should be abstract. Is not abstract because
	 * this class is used concretely for authorizing API calls
	 *
	 * @TODO Refactor, and make this abstract
	 * @param  string $status
	 * @return array
	 */
	protected function _get_list_from_api(string $status): array
	{
		return [];
	}
}
// End of BaseApiModel.php