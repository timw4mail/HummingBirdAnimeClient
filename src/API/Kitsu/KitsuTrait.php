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

namespace Aviat\AnimeClient\API\Kitsu;

use const Aviat\AnimeClient\SESSION_SEGMENT;

use function Amp\wait;

use Amp\Artax\{Client, Request};
use Aviat\AnimeClient\AnimeClient;
use Aviat\AnimeClient\API\Kitsu as K;
use Aviat\Ion\Json;

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
		}
		else if ($sessionSegment->get('auth_token') === NULL && $cacheItem->isHit())
		{
			$token = $cacheItem->get();
		}

		if ( ! is_null($token))
		{
			$request = $request->setAuth('bearer', $token);
		}

		if (array_key_exists('form_params', $options))
		{
			$request->setFormFields($options['form_params']);
		}

		if (array_key_exists('query', $options))
		{
			$request->setQuery($options['query']);
		}

		if (array_key_exists('body', $options))
		{
			$request->setJsonBody($options['body']);
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
		$request = $this->setUpRequest($type, $url, $options);

		$response = wait((new Client)->request($request));

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
			$logger = $this->container->getLogger('kitsu-request');
		}

		$response = $this->getResponse($type, $url, $options);

		if ((int) $response->getStatus() > 299 OR (int) $response->getStatus() < 200)
		{
			if ($logger)
			{
				$logger->warning('Non 200 response for api call', (array)$response->getBody());
			}
		}

		return Json::decode($response->getBody(), TRUE);
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

		if ( ! in_array((int) $response->getStatus(), $validResponseCodes))
		{
			if ($logger)
			{
				$logger->warning('Non 201 response for POST api call', $response->getBody());
			}
		}

		return JSON::decode($response->getBody(), TRUE);
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