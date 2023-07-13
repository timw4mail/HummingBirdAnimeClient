<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @copyright   2015 - 2022  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshome.page/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu;

use Amp\Http\Client\{Request, Response};
use Aviat\AnimeClient\API\APIRequestBuilder;
use Aviat\AnimeClient\Kitsu as K;
use Aviat\Ion\Di\{ContainerAware, ContainerInterface};
use Aviat\Ion\{Event, Json, JsonException};

use LogicException;
use function Amp\Promise\wait;
use function Aviat\AnimeClient\getResponse;
use function in_array;
use const Aviat\AnimeClient\{SESSION_SEGMENT, USER_AGENT};

final class RequestBuilder extends APIRequestBuilder
{
	use ContainerAware;

	/**
	 * The base url for api requests
	 */
	protected string $baseUrl = K::GRAPHQL_ENDPOINT;

	/**
	 * Where to look for GraphQL request files
	 */
	protected string $filePath = __DIR__;

	/**
	 * HTTP headers to send with every request
	 */
	protected array $defaultHeaders = [
		'User-Agent' => USER_AGENT,
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
	 */
	public function setUpRequest(string $type, string $url, array $options = []): Request
	{
		$request = $this->newRequest($type, $url);

		$sessionSegment = $this->getContainer()
			->get('session')
			->getSegment(SESSION_SEGMENT);

		$cache = $this->getContainer()->get('cache');
		$token = NULL;

		if ($cache->has(K::AUTH_TOKEN_CACHE_KEY))
		{
			$token = $cache->get(K::AUTH_TOKEN_CACHE_KEY);
		}
		elseif ($url !== K::AUTH_URL && $sessionSegment->get('auth_token') !== NULL)
		{
			$token = $sessionSegment->get('auth_token');
			if ( ! empty($token))
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
	 * @return mixed[]
	 */
	public function runQuery(string $name, array $variables = []): array
	{
		$request = $this->queryRequest($name, $variables);
		$response = getResponse($request);
		$validResponseCodes = [200, 201];

		if ( ! in_array($response->getStatus(), $validResponseCodes, TRUE))
		{
			$logger = $this->container->getLogger('kitsu-graphql');
			if ($logger !== NULL)
			{
				$logger->warning('Non 200 response for GraphQL call', (array) $response->getBody());
			}
		}

		return Json::decode(wait($response->getBody()->buffer()));
	}

	/**
	 * Run a GraphQL mutation
	 *
	 * @return mixed[]
	 */
	public function mutate(string $name, array $variables = []): array
	{
		$request = $this->mutateRequest($name, $variables);
		$response = getResponse($request);
		$validResponseCodes = [200, 201];

		if ( ! in_array($response->getStatus(), $validResponseCodes, TRUE))
		{
			$logger = $this->container->getLogger('kitsu-graphql');
			if ($logger !== NULL)
			{
				$logger->warning('Non 200 response for GraphQL call', (array) $response->getBody());
			}
		}

		return Json::decode(wait($response->getBody()->buffer()));
	}

	/**
	 * Make a request
	 */
	public function getResponse(string $type, string $url, array $options = []): Response
	{
		$logger = $this->container->getLogger('kitsu-request');
		$request = $this->setUpRequest($type, $url, $options);
		$response = getResponse($request);

		$logger?->debug('Kitsu API Response', [
			'status' => $response->getStatus(),
			'reason' => $response->getReason(),
			'body' => $response->getBody(),
			'headers' => $response->getHeaders(),
			'requestHeaders' => $request->getHeaders(),
		]);

		return $response;
	}

	/**
	 * Create a GraphQL query and return the Request object
	 */
	public function queryRequest(string $name, array $variables = []): Request
	{
		$file = realpath("{$this->filePath}/Queries/{$name}.graphql");
		if ($file === FALSE || ! file_exists($file))
		{
			throw new LogicException('GraphQL query file does not exist.');
		}

		$query = file_get_contents($file);
		$body = [
			'query' => $query,
		];

		if ( ! empty($variables))
		{
			$body['variables'] = [];

			foreach ($variables as $key => $val)
			{
				$body['variables'][$key] = $val;
			}
		}

		return $this->setUpRequest('POST', $this->baseUrl, [
			'body' => $body,
		]);
	}

	/**
	 * Create a GraphQL mutation request, and return the Request object
	 */
	public function mutateRequest(string $name, array $variables = []): Request
	{
		$file = realpath("{$this->filePath}/Mutations/{$name}.graphql");
		if ($file === FALSE || ! file_exists($file))
		{
			throw new LogicException('GraphQL mutation file does not exist.');
		}

		$query = file_get_contents($file);
		$body = [
			'query' => $query,
		];

		if ( ! empty($variables))
		{
			$body['variables'] = [];

			foreach ($variables as $key => $val)
			{
				$body['variables'][$key] = $val;
			}
		}

		return $this->setUpRequest('POST', $this->baseUrl, [
			'body' => $body,
		]);
	}
}
