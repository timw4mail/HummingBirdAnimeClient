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
use Aviat\Ion\Di\ContainerAware;
use Aviat\Ion\Json;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use InvalidArgumentException;
use RuntimeException;

trait KitsuTrait {
	use ContainerAware;
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

	/**
	 * Make a request via Guzzle
	 *
	 * @param string $type
	 * @param string $url
	 * @param array $options
	 * @return array
	 */
	private function request(string $type, string $url, array $options = []): array
	{
		$validTypes = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

		if ( ! in_array($type, $validTypes))
		{
			throw new InvalidArgumentException('Invalid http request type');
		}

		$logger = NULL;

		if ($this->getContainer())
		{
			$logger = $this->container->getLogger('default');
		}

		$defaultOptions = [
			'headers' => [
				'client_id' => 'dd031b32d2f56c990b1425efe6c42ad847e7fe3ab46bf1299f05ecd856bdb7dd',
				'client_secret' => '54d7307928f63414defd96399fc31ba847961ceaecef3a5fd93144e960c0e151'
			]
		];

		$options = array_merge($defaultOptions, $options);

		$response = $this->client->request($type, $url, $options);

		if ((int) $response->getStatusCode() !== 200)
		{
			if ($logger)
			{
				$logger->warning('Non 200 response for api call');
				$logger->warning($response->getBody());
			}

			throw new RuntimeException($response);
		}

		return JSON::decode($response->getBody(), TRUE);
	}

	/**
	 * Remove some boilerplate for get requests
	 *
	 * @param array $args
	 * @return array
	 */
	protected function getRequest(...$args): array
	{
		return $this->request('GET', ...$args);
	}

	/**
	 * Remove some boilerplate for get requests
	 *
	 * @param array $args
	 * @return array
	 */
	protected function postRequest(...$args): array
	{
		return $this->request('POST', ...$args);
	}
}