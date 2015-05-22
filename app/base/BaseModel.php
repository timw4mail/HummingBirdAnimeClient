<?php

use \GuzzleHttp\Client;
use \GuzzleHttp\Cookie\CookieJar;

class BaseModel {

	protected $client;
	protected $cookieJar;

	public function __construct()
	{
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
	 * Get the full url path, since the base_url in Guzzle doesn't work correctly
	 *
	 * @param string $path
	 * @return string
	 */
	protected function _url($path)
	{
		return "{$this->base_url}{$path}";
	}
}
// End of BaseModel.php