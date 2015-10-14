<?php
/**
 * Base API Model
 */
namespace Aviat\AnimeClient\Model;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Psr7\Request;

use Aviat\Ion\Di\ContainerInterface;
use Aviat\AnimeClient\Model as BaseModel;

/**
 * Base model for api interaction
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
			'defaults' => [
				'cookies' => $this->cookieJar,
				'headers' => [
					'User-Agent' => $_SERVER['HTTP_USER_AGENT'],
					'Accept-Encoding' => 'application/json'
				],
				'timeout' => 5,
				'connect_timeout' => 5
			]
		]);
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
		$result = $this->client->post('https://hummingbird.me/api/v1/users/authenticate', [
			'body' => [
				'username' => $username,
				'password' => $password
			]
		]);

		if ($result->getStatusCode() === 201)
		{
			return json_decode($result->getBody(), TRUE);
		}

		return FALSE;
	}
}
// End of BaseApiModel.php