<?php
/**
 * Base API Model
 */
namespace AnimeClient\Base;

use \GuzzleHttp\Client;
use \GuzzleHttp\Cookie\CookieJar;

/**
 * Base model for api interaction
 */
class ApiModel extends Model {

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
	 */
	public function __construct(Config $config)
	{
		parent::__construct($config);

		$this->cookieJar = new CookieJar();
		$this->client = new Client([
			'base_url' => $this->base_url,
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
	 * @return bool
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
			$_SESSION['hummingbird_anime_token'] = $result->json();
			return TRUE;
		}

		return FALSE;
	}
}
// End of BaseApiModel.php