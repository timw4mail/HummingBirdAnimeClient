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

namespace Aviat\AnimeClient\API\Kitsu;

use Aviat\AnimeClient\AnimeClient;
use Aviat\AnimeClient\API\GuzzleTrait;
use Aviat\AnimeClient\API\Kitsu as K;
use Aviat\Ion\Json;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use RuntimeException;

trait KitsuTrait {
	use GuzzleTrait;

	/**
	 * The base url for api requests
	 * @var string $base_url
	 */
	protected $baseUrl = "https://kitsu.io/api/edge/";

	/**
	 * HTTP headers to send with every request
	 *
	 * @var array
	 */
	protected $defaultHeaders = [
		'User-Agent' => "Tim's Anime Client/4.0",
		'Accept-Encoding' => 'application/vnd.api+json',
		'Content-Type' => 'application/vnd.api+json',
		'client_id' => 'dd031b32d2f56c990b1425efe6c42ad847e7fe3ab46bf1299f05ecd856bdb7dd',
		'client_secret' => '54d7307928f63414defd96399fc31ba847961ceaecef3a5fd93144e960c0e151',
	];

	/**
	 * Set up the class properties
	 *
	 * @return void
	 */
	protected function init()
	{
		$defaults = [
			'cookies' => $this->cookieJar,
			'headers' => $this->defaultHeaders,
			'timeout' => 25,
			'connect_timeout' => 25
		];

		$this->cookieJar = new CookieJar();
		$this->client = new Client([
			'base_uri' => $this->baseUrl,
			'cookies' => TRUE,
			'http_errors' => TRUE,
			'defaults' => $defaults
		]);
	}

	/**
	 * Make a request via Guzzle
	 *
	 * @param string $type
	 * @param string $url
	 * @param array $options
	 * @return Response
	 */
	private function getResponse(string $type, string $url, array $options = [])
	{
		$logger = null;
		$validTypes = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

		if ( ! in_array($type, $validTypes))
		{
			throw new InvalidArgumentException('Invalid http request type');
		}

		$defaultOptions = [
			'headers' => $this->defaultHeaders
		];

		$logger = $this->container->getLogger('request');
		$sessionSegment = $this->getContainer()
			->get('session')
			->getSegment(AnimeClient::SESSION_SEGMENT);

		if ($sessionSegment->get('auth_token') !== null && $url !== K::AUTH_URL)
		{
			$token = $sessionSegment->get('auth_token');
			$defaultOptions['headers']['Authorization'] = "bearer {$token}";
		}

		$options = array_merge($defaultOptions, $options);

		$logger->debug(Json::encode([$type, $url]));
		$logger->debug(Json::encode($options));

		return $this->client->request($type, $url, $options);
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
		$logger = null;
		if ($this->getContainer())
		{
			$logger = $this->container->getLogger('request');
		}

		$response = $this->getResponse($type, $url, $options);

		if ((int) $response->getStatusCode() > 299 || (int) $response->getStatusCode() < 200)
		{
			if ($logger)
			{
				$logger->warning('Non 200 response for api call');
				$logger->warning($response->getBody());
			}

			// throw new RuntimeException($response->getBody());
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
	 * Remove some boilerplate for patch requests
	 *
	 * @param array $args
	 * @return array
	 */
	protected function patchRequest(...$args): array
	{
		return $this->request('PATCH', ...$args);
	}

	/**
	 * Remove some boilerplate for post requests
	 *
	 * @param array $args
	 * @return array
	 */
	protected function postRequest(...$args): array
	{
		$logger = null;
		if ($this->getContainer())
		{
			$logger = $this->container->getLogger('request');
		}

		$response = $this->getResponse('POST', ...$args);
		$validResponseCodes = [200, 201];

		if ( ! in_array((int) $response->getStatusCode(), $validResponseCodes))
		{
			if ($logger)
			{
				$logger->warning('Non 201 response for POST api call');
				$logger->warning($response->getBody());
			}

			// throw new RuntimeException($response->getBody());
		}

		return JSON::decode($response->getBody(), TRUE);
	}

	/**
	 * Remove some boilerplate for delete requests
	 *
	 * @param array $args
	 * @return bool
	 */
	protected function deleteRequest(...$args): bool
	{
		$response = $this->getResponse('DELETE', ...$args);
		return ((int) $response->getStatusCode() === 204);
	}
}