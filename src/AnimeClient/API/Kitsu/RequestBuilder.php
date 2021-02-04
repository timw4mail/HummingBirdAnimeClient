<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2021  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu;

use const Aviat\AnimeClient\SESSION_SEGMENT;
use const Aviat\AnimeClient\USER_AGENT;

use function Amp\Promise\wait;
use function Aviat\AnimeClient\getResponse;

use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Aviat\AnimeClient\Kitsu as K;
use Aviat\AnimeClient\API\APIRequestBuilder;
use Aviat\AnimeClient\API\FailedResponseException;
use Aviat\AnimeClient\Enum\EventType;
use Aviat\Ion\Di\ContainerAware;
use Aviat\Ion\Di\ContainerInterface;
use Aviat\Ion\Event;
use Aviat\Ion\Json;
use Aviat\Ion\JsonException;

use LogicException;

final class RequestBuilder extends APIRequestBuilder {
	use ContainerAware;

	/**
	 * The base url for api requests
	 * @var string $base_url
	 */
	protected string $baseUrl = K::GRAPHQL_ENDPOINT;

	/**
	 * Where to look for GraphQL request files
	 * @var string
	 */
	protected string $filePath = __DIR__;

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
	 * Run a GraphQL API query
	 *
	 * @param string $name
	 * @param array $variables
	 * @return array
	 */
	public function runQuery(string $name, array $variables = []): array
	{
		$request = $this->queryRequest($name, $variables);
		$response = getResponse($request);
		$validResponseCodes = [200, 201];

		if ( ! \in_array($response->getStatus(), $validResponseCodes, TRUE))
		{
			$logger = $this->container->getLogger('kitsu-graphql');
			$logger->warning('Non 200 response for GraphQL call', (array)$response->getBody());
		}

		return Json::decode(wait($response->getBody()->buffer()));
	}

	/**
	 * Run a GraphQL mutation
	 *
	 * @param string $name
	 * @param array $variables
	 * @return array
	 * @throws Throwable
	 */
	public function mutate(string $name, array $variables = []): array
	{
		$request = $this->mutateRequest($name, $variables);
		$response = getResponse($request);
		$validResponseCodes = [200, 201];

		if ( ! \in_array($response->getStatus(), $validResponseCodes, TRUE))
		{
			$logger = $this->container->getLogger('kitsu-graphql');
			$logger->warning('Non 200 response for GraphQL call', (array)$response->getBody());
		}

		return Json::decode(wait($response->getBody()->buffer()));
	}

	/**
	 * Make a request
	 *
	 * @param string $type
	 * @param string $url
	 * @param array $options
	 * @return Response
	 * @throws \Throwable
	 */
	public function getResponse(string $type, string $url, array $options = []): Response
	{
		$logger = $this->container->getLogger('kitsu-request');
		$request = $this->setUpRequest($type, $url, $options);
		$response = getResponse($request);

		$logger->debug('Kitsu API Response', [
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
	 * @throws JsonException
	 * @throws FailedResponseException
	 * @throws Throwable
	 * @return array
	 */
	private function request(string $type, string $url, array $options = []): array
	{
		$logger = $this->container->getLogger('kitsu-request');
		$response = $this->getResponse($type, $url, $options);
		$statusCode = $response->getStatus();

		// Check for requests that are unauthorized
		if ($statusCode === 401 || $statusCode === 403)
		{
			Event::emit(EventType::UNAUTHORIZED);
		}

		$rawBody = wait($response->getBody()->buffer());

		// Any other type of failed request
		if ($statusCode > 299 || $statusCode < 200)
		{
			$logger->warning('Non 2xx response for api call', (array)$response);
		}

		try
		{
			return Json::decode($rawBody);
		}
		catch (JsonException $e)
		{
			// dump($e);
			dump($rawBody);
			die();
		}
	}
}