<?php
/**
 * Base API Model
 */
namespace Aviat\AnimeClient\Model;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\ResponseInterface;
use GuzzleHttp\Exception\ClientException;

use Aviat\Ion\Di\ContainerInterface;
use Aviat\AnimeClient\Model as BaseModel;

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
class API extends BaseModel {

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
	 * Constructor
	 *
	 * @param ContainerInterface $container
	 */
	public function __construct(ContainerInterface $container)
	{
		parent::__construct($container);
		$this->cookieJar = new CookieJar();
		$this->client = new Client([
			'base_uri' => $this->base_url,
			'cookies' => TRUE,
			'http_errors' => FALSE,
			'defaults' => [
				'cookies' => $this->cookieJar,
				'headers' => [
					'User-Agent' => "Tim's Anime Client/2.0",
					'Accept-Encoding' => 'application/json'
				],
				'timeout' => 5,
				'connect_timeout' => 5
			]
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
			'delete',
			'head',
			'options',
			'patch',
			'post',
			'put'
		];

		if ( ! in_array($method, $valid_methods))
		{
			return NULL;
		}

		array_unshift($args, strtoupper($method));
		return call_user_func_array([$this->client, 'request'], $args);
	}

	/**
	 * Attempt login via the api
	 *
	 * @codeCoverageIgnore
	 * @param string $username
	 * @param string $password
	 * @return string|false
	 */
	public function authenticate($username, $password)
	{
		$response = $this->post('https://hummingbird.me/api/v1/users/authenticate', [
			'form_params' => [
				'username' => $username,
				'password' => $password
			]
		]);

		if ($response->getStatusCode() === 201)
		{
			return json_decode($response->getBody(), TRUE);
		}

		return FALSE;
	}
}
// End of BaseApiModel.php