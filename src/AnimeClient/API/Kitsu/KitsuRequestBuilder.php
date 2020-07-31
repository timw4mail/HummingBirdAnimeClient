<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.4
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu;

use const Aviat\AnimeClient\SESSION_SEGMENT;
use const Aviat\AnimeClient\USER_AGENT;

use function Amp\Promise\wait;
use function Aviat\AnimeClient\getResponse;

use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Aviat\AnimeClient\API\APIRequestBuilder;
use Aviat\AnimeClient\API\FailedResponseException;
use Aviat\AnimeClient\API\Kitsu as K;
use Aviat\AnimeClient\Enum\EventType;
use Aviat\Ion\Di\ContainerAware;
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Event;
use Aviat\Ion\Json;
use Aviat\Ion\JsonException;

final class KitsuRequestBuilder extends APIRequestBuilder {
	use ContainerAware;

	/**
	 * The base url for api requests
	 * @var string $base_url
	 */
	protected string $baseUrl = K::JSON_API_ENDPOINT;

	/**
	 * HTTP headers to send with every request
	 *
	 * @var array
	 */
	protected array $defaultHeaders = [
		'User-Agent' =>  USER_AGENT,
		'Accept' => 'application/vnd.api+json',
		'Content-Type' => 'application/vnd.api+json',
		'CLIENT_ID' => 'dd031b32d2f56c990b1425efe6c42ad847e7fe3ab46bf1299f05ecd856bdb7dd',
		'CLIENT_SECRET' => '54d7307928f63414defd96399fc31ba847961ceaecef3a5fd93144e960c0e151',
	];

	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);
	}

	/**
	 * Create a request object
	 *
	 * @param string $type
	 * @param string $url
	 * @param array $options
	 * @return Request
	 */
	public function setUpRequest(string $type, string $url, array $options = []): Request
	{
		$request = $this->newRequest($type, $url);

		$sessionSegment = $this->getContainer()
			->get('session')
			->getSegment(SESSION_SEGMENT);

		$cache = $this->getContainer()->get('cache');
		$token = null;

		if ($cache->has(K::AUTH_TOKEN_CACHE_KEY))
		{
			$token = $cache->get(K::AUTH_TOKEN_CACHE_KEY);
		}
		else if ($url !== K::AUTH_URL && $sessionSegment->get('auth_token') !== NULL)
		{
			$token = $sessionSegment->get('auth_token');
			if ( ! (empty($token) || $cache->has(K::AUTH_TOKEN_CACHE_KEY)))
			{
				$cache->set(K::AUTH_TOKEN_CACHE_KEY, $token);
			}
		}

		if ($token !== NULL)
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
	 * Remove some boilerplate for get requests
	 *
	 * @param mixed ...$args
	 * @throws Throwable
	 * @return array
	 */
	public function getRequest(...$args): array
	{
		return $this->request('GET', ...$args);
	}

	/**
	 * Remove some boilerplate for patch requests
	 *
	 * @param mixed ...$args
	 * @throws Throwable
	 * @return array
	 */
	public function patchRequest(...$args): array
	{
		return $this->request('PATCH', ...$args);
	}

	/**
	 * Remove some boilerplate for post requests
	 *
	 * @param mixed ...$args
	 * @throws Throwable
	 * @return array
	 */
	public function postRequest(...$args): array
	{
		$logger = NULL;
		if ($this->getContainer())
		{
			$logger = $this->container->getLogger('kitsu-request');
		}

		$response = $this->getResponse('POST', ...$args);
		$validResponseCodes = [200, 201];

		if ( ! in_array($response->getStatus(), $validResponseCodes, TRUE) && $logger)
		{
			$logger->warning('Non 2xx response for POST api call', $response->getBody());
		}

		return JSON::decode(wait($response->getBody()->buffer()), TRUE);
	}

	/**
	 * Remove some boilerplate for delete requests
	 *
	 * @param mixed ...$args
	 * @throws Throwable
	 * @return bool
	 */
	public function deleteRequest(...$args): bool
	{
		$response = $this->getResponse('DELETE', ...$args);
		return ($response->getStatus() === 204);
	}

	/**
	 * Run a GraphQL API query
	 *
	 * @param string $name
	 * @param array $variables
	 * @return array
	 */
	public function runQuery(string $name, array $variables = []): array
	{
		$file =__DIR__ . "/GraphQL/Queries/{$name}.graphql";
		if ( ! file_exists($file))
		{
			throw new LogicException('GraphQL query file does not exist.');
		}

		// $query = str_replace(["\t", "\n"], ' ', file_get_contents($file));
		$query = file_get_contents($file);
		$body = [
			'query' => $query
		];

		if ( ! empty($variables))
		{
			$body['variables'] = [];
			foreach($variables as $key => $val)
			{
				$body['variables'][$key] = $val;
			}
		}

		return $this->graphResponse([
			'body' => $body
		]);
	}

	/**
	 * @param string $name
	 * @param array $variables
	 * @return Request
	 * @throws Throwable
	 */
	public function mutateRequest (string $name, array $variables = []): Request
	{
		$file = __DIR__ . "/GraphQL/Mutations/{$name}.graphql";
		if ( ! file_exists($file))
		{
			throw new LogicException('GraphQL mutation file does not exist.');
		}

		// $query = str_replace(["\t", "\n"], ' ', file_get_contents($file));
		$query = file_get_contents($file);
		$body = [
			'query' => $query
		];

		if (!empty($variables)) {
			$body['variables'] = [];
			foreach ($variables as $key => $val)
			{
				$body['variables'][$key] = $val;
			}
		}

		return $this->setUpRequest('POST', K::GRAPHQL_ENDPOINT, [
			'body' => $body,
		]);
	}

	/**
	 * @param string $name
	 * @param array $variables
	 * @return array
	 * @throws Throwable
	 */
	public function mutate(string $name, array $variables = []): array
	{
		$request = $this->mutateRequest($name, $variables);
		$response = $this->getResponseFromRequest($request);

		return Json::decode(wait($response->getBody()->buffer()));
	}

	/**
	 * Make a request
	 *
	 * @param string $type
	 * @param string $url
	 * @param array $options
	 * @return Response
	 * @throws Throwable
	 */
	public function getResponse(string $type, string $url, array $options = []): Response
	{
		$logger = NULL;
		if ($this->getContainer())
		{
			$logger = $this->container->getLogger('kitsu-request');
		}

		$request = $this->setUpRequest($type, $url, $options);

		$response = getResponse($request);

		if ($logger)
		{
			$logger->debug('Kitsu API Response', [
				'status' => $response->getStatus(),
				'reason' => $response->getReason(),
				'body' => $response->getBody(),
				'headers' => $response->getHeaders(),
				'requestHeaders' => $request->getHeaders(),
			]);
		}

		return $response;
	}

	/**
	 * @param Request $request
	 * @return Response
	 * @throws Throwable
	 */
	private function getResponseFromRequest(Request $request): Response
	{
		$logger = NULL;
		if ($this->getContainer())
		{
			$logger = $this->container->getLogger('kitsu-request');
		}

		$response = getResponse($request);

		$logger->debug('Kitsu GraphQL response', [
			'status' => $response->getStatus(),
			'reason' => $response->getReason(),
			'body' => $response->getBody(),
			'headers' => $response->getHeaders(),
			'requestHeaders' => $request->getHeaders(),
		]);

		return $response;
	}

	/**
	 * Remove some boilerplate for GraphQL requests
	 *
	 * @param array $options
	 * @return array
	 * @throws \Throwable
	 */
	protected function graphResponse(array $options = []): array
	{
		$response = $this->getResponse('POST', K::GRAPHQL_ENDPOINT, $options);
		$validResponseCodes = [200, 201];

		$logger = NULL;
		if ($this->getContainer())
		{
			$logger = $this->container->getLogger('kitsu-request');
			$logger->debug('Kitsu GraphQL response', [
				'status' => $response->getStatus(),
				'reason' => $response->getReason(),
				'body' => $response->getBody(),
				'headers' => $response->getHeaders(),
			]);
		}

		if ( ! \in_array($response->getStatus(), $validResponseCodes, TRUE))
		{
			if ($logger !== NULL)
			{
				$logger->warning('Non 200 response for GraphQL call', (array)$response->getBody());
			}
		}

		return Json::decode(wait($response->getBody()->buffer()));
	}

	/**
	 * Make a request
	 *
	 * @param string $type
	 * @param string $url
	 * @param array $options
	 * @throws JsonException
	 * @throws FailedResponseException
	 * @throws Throwable
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
		$statusCode = $response->getStatus();

		// Check for requests that are unauthorized
		if ($statusCode === 401 || $statusCode === 403)
		{
			Event::emit(EventType::UNAUTHORIZED);
		}

		// Any other type of failed request
		if ($statusCode > 299 || $statusCode < 200)
		{
			if ($logger)
			{
				$logger->warning('Non 2xx response for api call', (array)$response);
			}

			throw new FailedResponseException('Failed to get the proper response from the API');
		}

		try
		{
			return Json::decode(wait($response->getBody()->buffer()));
		}
		catch (JsonException $e)
		{
			print_r($e);
			die();
		}
	}


}