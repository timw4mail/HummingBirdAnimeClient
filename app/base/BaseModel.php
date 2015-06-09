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

	/**
	 * Get the path of the cached version of the image. Create the cached image
	 * if the file does not already exist
	 *
	 * @param string $api_path - The original image url
	 * @param string $series_slug - The part of the url with the series name, becomes the image name
	 * @param string $type - Anime or Manga, controls cache path
	 * @return string - the frontend path for the cached image
	 */
	public function get_cached_image($api_path, $series_slug, $type="anime")
	{
		$path_parts = explode('?', basename($api_path));
		$path = current($path_parts);
		$ext_parts = explode('.', $path);
		$ext = end($ext_parts);

		$cached_image = "{$series_slug}.{$ext}";
		$cached_path = __DIR__ . "/../../public/cache/{$type}/{$cached_image}";

		// Cache the file if it doesn't already exist
		if ( ! file_exists($cached_path))
		{
			if (ini_get('allow_url_fopen'))
			{
				copy($api_path, $cached_path);
			}
			elseif (function_exists('curl_init'))
			{
				$ch = curl_init($api_path);
				$fp = fopen($cached_path, 'wb');
				curl_setopt_array($ch, [
					CURLOPT_FILE => $fp,
					CURLOPT_HEADER => 0
				]);
				curl_exec($ch);
				curl_close($ch);
				fclose($ch);
			}
			else
			{
				throw new Exception("Couldn't cache images because they couldn't be downloaded.");
			}
		}

		return "/public/cache/{$type}/{$cached_image}";
	}
}
// End of BaseModel.php