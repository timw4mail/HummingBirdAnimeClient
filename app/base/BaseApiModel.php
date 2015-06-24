<?php
/**
 * Base API Model
 */

use \GuzzleHttp\Client;
use \GuzzleHttp\Cookie\CookieJar;

/**
 * Base model for api interaction
 */
class BaseApiModel extends BaseModel {

	/**
	 * The Guzzle http client object
	 * @var object $client
	 */
	protected $client;

	/**
	 * Cookie jar object for api requests
	 * @var object $cookieJar
	 */
	protected $cookieJar;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();

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
	 * @param string $username
	 * @param string $password
	 * @return bool
	 */
	public function authenticate($username, $password)
	{
		$result = $this->client->post('https://hummingbird.me/api/v1/users/authenticate', [
			'body' => [
				'username' => $this->config->hummingbird_username,
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