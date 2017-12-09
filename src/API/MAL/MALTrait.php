<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2017  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\MAL;

use function Amp\Promise\wait;

use Aviat\AnimeClient\API\{
	HummingbirdClient,
	MAL as M,
	XML
};

trait MALTrait {

	/**
	 * The request builder for the MAL API
	 * @var MALRequestBuilder
	 */
	protected $requestBuilder;

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
		'Accept' => 'text/xml',
		'Accept-Encoding' => 'gzip',
		'Content-type' => 'application/x-www-form-urlencoded',
		'User-Agent' => "Tim's Anime Client/4.0"
	];

	/**
	 * Set the request builder object
	 *
	 * @param MALRequestBuilder $requestBuilder
	 * @return self
	 */
	public function setRequestBuilder($requestBuilder): self
	{
		$this->requestBuilder = $requestBuilder;
		return $this;
	}

	/**
	 * Create a request object
	 *
	 * @param string $type
	 * @param string $url
	 * @param array $options
	 * @return \Amp\Artax\Response
	 */
	public function setUpRequest(string $type, string $url, array $options = [])
	{
		$config = $this->container->get('config');

		$request = $this->requestBuilder
			->newRequest($type, $url)
			->setBasicAuth($config->get(['mal','username']), $config->get(['mal','password']));

		if (array_key_exists('query', $options))
		{
			$request = $request->setQuery($options['query']);
		}

		if (array_key_exists('body', $options))
		{
			$request = $request->setBody($options['body']);
		}

		return $request->getFullRequest();
	}

	/**
	 * Make a request
	 *
	 * @param string $type
	 * @param string $url
	 * @param array $options
	 * @return \Amp\Artax\Response
	 */
	private function getResponse(string $type, string $url, array $options = [])
	{
		$logger = NULL;
		if ($this->getContainer())
		{
			$logger = $this->container->getLogger('mal-request');
		}

		$request = $this->setUpRequest($type, $url, $options);
		$response = wait((new HummingbirdClient)->request($request));

		$logger->debug('MAL api response', [
			'status' => $response->getStatus(),
			'reason' => $response->getReason(),
			'body' => $response->getBody(),
			'headers' => $response->getHeaders(),
			'requestHeaders' => $request->getHeaders(),
		]);

		return $response;
	}

	/**
	 * Make a request
	 *
	 * @param string $type
	 * @param string $url
	 * @param array $options
	 * @return array
	 */
	private function request(string $type, string $url, array $options = []): array
	{
		$logger = NULL;
		if ($this->getContainer())
		{
			$logger = $this->container->getLogger('mal-request');
		}

		$response = $this->getResponse($type, $url, $options);

		if ((int) $response->getStatus() > 299 OR (int) $response->getStatus() < 200)
		{
			if ($logger)
			{
				$logger->warning('Non 200 response for api call', $response->getBody());
			}
		}

		return XML::toArray(wait($response->getBody()));
	}

	/**
	 * Remove some boilerplate for get requests
	 *
	 * @param mixed ...$args
	 * @return array
	 */
	protected function getRequest(...$args): array
	{
		return $this->request('GET', ...$args);
	}

	/**
	 * Remove some boilerplate for post requests
	 *
	 * @param mixed ...$args
	 * @return array
	 */
	protected function postRequest(...$args): array
	{
		$logger = NULL;
		if ($this->getContainer())
		{
			$logger = $this->container->getLogger('mal-request');
		}

		$response = $this->getResponse('POST', ...$args);
		$validResponseCodes = [200, 201];

		if ( ! in_array((int) $response->getStatus(), $validResponseCodes))
		{
			if ($logger)
			{
				$logger->warning('Non 201 response for POST api call', $response->getBody());
			}
		}

		return XML::toArray($response->getBody());
	}
}