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
 * @copyright   2015 - 2018  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu;

use const Aviat\AnimeClient\SESSION_SEGMENT;

use function Amp\Promise\wait;

use Amp\Artax\Request;
use Aviat\AnimeClient\AnimeClient;
use Aviat\AnimeClient\API\{
	FailedResponseException,
	HummingbirdClient,
	Kitsu as K
};
use Aviat\Ion\Json;
use Aviat\Ion\JsonException;

trait KitsuTrait {

	/**
	 * The request builder for the MAL API
	 * @var KitsuRequestBuilder
	 */
	protected $requestBuilder;

	/**
	 * Set the request builder object
	 *
	 * @param KitsuRequestBuilder $requestBuilder
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
	 * @return \Amp\Artax\Request
	 */
	public function setUpRequest(string $type, string $url, array $options = []): Request
	{
		$request = $this->requestBuilder->newRequest($type, $url);

		$sessionSegment = $this->getContainer()
			->get('session')
			->getSegment(SESSION_SEGMENT);

		$cache = $this->getContainer()->get('cache');
		$cacheItem = $cache->getItem('kitsu-auth-token');
		$token = null;


		if ($sessionSegment->get('auth_token') !== NULL && $url !== K::AUTH_URL)
		{
			$token = $sessionSegment->get('auth_token');
			if ( ! $cacheItem->isHit())
			{
				$cacheItem->set($token);
				$cacheItem->save();
			}
		}
		else if ($sessionSegment->get('auth_token') === NULL && $cacheItem->isHit())
		{
			$token = $cacheItem->get();
		}

		if (NULL !== $token)
		{
			$request = $request->setAuth('bearer', $token);
		}

		if (array_key_exists('form_params', $options))
		{
			$request = $request->setFormFields($options['form_params']);
		}

		if (array_key_exists('query', $options))
		{
			$request = $request->setQuery($options['query']);
		}

		if (array_key_exists('body', $options))
		{
			$request = $request->setJsonBody($options['body']);
		}

		if (array_key_exists('headers', $options))
		{
			$request = $request->setHeaders($options['headers']);
		}

		return $request->getFullRequest();
	}

	/**
	 * Make a request
	 *
	 * @param string $type
	 * @param string $url
	 * @param array $options
	 * @return Response
	 */
	private function getResponse(string $type, string $url, array $options = [])
	{
		$logger = NULL;
		if ($this->getContainer())
		{
			$logger = $this->container->getLogger('kitsu-request');
		}

		$request = $this->setUpRequest($type, $url, $options);

		$response = wait((new HummingbirdClient)->request($request));

		if ($logger)
		{
			$logger->debug('Kitsu API Response', [
				'response_status' => $response->getStatus(),
				'request_headers' => $response->getOriginalRequest()->getHeaders(),
				'response_headers' => $response->getHeaders()
			]);
		}

		return $response;
	}

	/**
	 * Make a request
	 *
	 * @param string $type
	 * @param string $url
	 * @param array $options
	 * @throws \Aviat\Ion\JsonException
	 * @throws FailedResponseException
	 * @return array
	 */
	private function request(string $type, string $url, array $options = []): array
	{
		$logger = NULL;
		if ($this->getContainer())
		{
			$logger = $this->container->getLogger('kitsu-request');
		}

		$response = $this->getResponse($type, $url, $options);

		if ((int) $response->getStatus() > 299 OR (int) $response->getStatus() < 200)
		{
			if ($logger)
			{
				$logger->warning('Non 200 response for api call', (array)$response);
			}

			throw new FailedResponseException('Failed to get the proper response from the API');
		}

		try
		{
			return Json::decode(wait($response->getBody()));
		}
		catch (JsonException $e)
		{
			print_r($e);
			die();
		}

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
	 * Remove some boilerplate for patch requests
	 *
	 * @param mixed ...$args
	 * @return array
	 */
	protected function patchRequest(...$args): array
	{
		return $this->request('PATCH', ...$args);
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
			$logger = $this->container->getLogger('kitsu-request');
		}

		$response = $this->getResponse('POST', ...$args);
		$validResponseCodes = [200, 201];

		if ( ! \in_array((int) $response->getStatus(), $validResponseCodes, TRUE))
		{
			if ($logger)
			{
				$logger->warning('Non 201 response for POST api call', $response->getBody());
			}
		}

		return JSON::decode(wait($response->getBody()), TRUE);
	}

	/**
	 * Remove some boilerplate for delete requests
	 *
	 * @param mixed ...$args
	 * @return bool
	 */
	protected function deleteRequest(...$args): bool
	{
		$response = $this->getResponse('DELETE', ...$args);
		return ((int) $response->getStatus() === 204);
	}
}