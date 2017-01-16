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
 * @copyright   2015 - 2017  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\MAL;

use Aviat\AnimeClient\API\{
	GuzzleTrait,
	MAL as M,
	XML
};
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;

trait MALTrait {
	use GuzzleTrait;

	/**
	 * The base url for api requests
	 * @var string $base_url
	 */
	protected $baseUrl = M::BASE_URL;

	/**
	 * HTTP headers to send with every request
	 *
	 * @var array
	 */
	protected $defaultHeaders = [
		'User-Agent' => "Tim's Anime Client/4.0"
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
		$type = strtoupper($type);
		$validTypes = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

		if ( ! in_array($type, $validTypes))
		{
			throw new InvalidArgumentException('Invalid http request type');
		}
		
		$config = $this->container->get('config');
		$logger = $this->container->getLogger('request');

		$defaultOptions = [
			'auth' => [
				$config->get(['mal','username']), 
				$config->get(['mal','password'])
			],
			'headers' => $this->defaultHeaders
		];

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

		return XML::toArray((string) $response->getBody());
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
		}

		return XML::toArray((string) $response->getBody());
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