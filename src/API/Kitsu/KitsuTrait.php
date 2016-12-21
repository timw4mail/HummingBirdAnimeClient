<?php declare(strict_types=1);
/**
 * Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package	 AnimeListClient
 * @author	  Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2016  Timothy J. Warren
 * @license	 http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version	 4.0
 * @link		https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu;

use Aviat\AnimeClient\API\GuzzleTrait;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;

trait KitsuTrait {
	use GuzzleTrait;

	/**
	 * The base url for api requests
	 * @var string $base_url
	 */
	protected $baseUrl = "https://kitsu.io/api/edge/";

	/**
	 * Set up the class properties
	 *
	 * @return void
	 */
	protected function init()
	{
		$this->cookieJar = new CookieJar();
		$this->client = new Client([
			'base_uri' => $this->baseUrl,
			'cookies' => TRUE,
			'http_errors' => TRUE,
			'defaults' => [
				'cookies' => $this->cookieJar,
				'headers' => [
					'User-Agent' => "Tim's Anime Client/4.0",
					'Accept-Encoding' => 'application/vnd.api+json',
					'Content-Type' => 'application/vnd.api+json'
				],
				'timeout' => 25,
				'connect_timeout' => 25
			]
		]);
	}
}