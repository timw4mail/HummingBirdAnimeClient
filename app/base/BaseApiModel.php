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
}
// End of BaseApiModel.php