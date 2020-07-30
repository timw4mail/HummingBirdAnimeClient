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

use Amp\Http\Client\Request;
use Amp\Http\Client\Response;
use Aviat\AnimeClient\API\Anilist;
use Aviat\AnimeClient\API\Kitsu as K;
use Aviat\Ion\Di\ContainerAware;
use Aviat\Ion\Di\ContainerInterface;

use Aviat\Ion\Json;
use function Amp\Promise\wait;
use function Aviat\AnimeClient\getResponse;
use const Aviat\AnimeClient\SESSION_SEGMENT;
use const Aviat\AnimeClient\USER_AGENT;

use Aviat\AnimeClient\API\APIRequestBuilder;

final class KitsuRequestBuilder extends APIRequestBuilder {
	use ContainerAware;

	/**
	 * The base url for api requests
	 * @var string $base_url
	 */
	protected string $baseUrl = 'https://kitsu.io/api/graphql';

	/**
	 * Valid HTTP request methods
	 * @var array
	 */
	protected array $validMethods = ['POST'];

	/**
	 * HTTP headers to send with every request
	 *
	 * @var array
	 */
	protected array $defaultHeaders = [
		'User-Agent' => USER_AGENT,
		'Accept' => 'application/json',
		'Content-Type' => 'application/json',
	];

	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);
	}

	/**
	 * Create a request object
	 * @param string $url
	 * @param array $options
	 * @return Request
	 * @throws Throwable
	 */
	public function setUpRequest(string $url, array $options = []): Request
	{
		$request = $this->newRequest('POST', $url);

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
		$file = realpath(__DIR__ . "/GraphQL/Queries/{$name}.graphql");
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

		return $this->postRequest([
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
		$file = realpath(__DIR__ . "/GraphQL/Mutations/{$name}.graphql");
		if (!file_exists($file))
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

		return $this->setUpRequest(Anilist::BASE_URL, [
			'body' => $body,
		]);
	}

	/**
	 * @param string $name
	 * @param array $variables
	 * @return array
	 * @throws Throwable
	 */
	public function mutate (string $name, array $variables = []): array
	{
		$request = $this->mutateRequest($name, $variables);
		$response = $this->getResponseFromRequest($request);

		return Json::decode(wait($response->getBody()->buffer()));
	}

	/**
	 * Make a request
	 *
	 * @param string $url
	 * @param array $options
	 * @return Response
	 * @throws Throwable
	 */
	private function getResponse(string $url, array $options = []): Response
	{
		$logger = NULL;
		if ($this->getContainer())
		{
			$logger = $this->container->getLogger('anilist-request');
		}

		$request = $this->setUpRequest($url, $options);
		$response = getResponse($request);

		$logger->debug('Anilist response', [
			'status' => $response->getStatus(),
			'reason' => $response->getReason(),
			'body' => $response->getBody(),
			'headers' => $response->getHeaders(),
			'requestHeaders' => $request->getHeaders(),
		]);

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
			$logger = $this->container->getLogger('anilist-request');
		}

		$response = getResponse($request);

		$logger->debug('Anilist response', [
			'status' => $response->getStatus(),
			'reason' => $response->getReason(),
			'body' => $response->getBody(),
			'headers' => $response->getHeaders(),
			'requestHeaders' => $request->getHeaders(),
		]);

		return $response;
	}

	/**
	 * Remove some boilerplate for post requests
	 *
	 * @param array $options
	 * @return array
	 * @throws Throwable
	 */
	protected function postRequest(array $options = []): array
	{
		$response = $this->getResponse($this->baseUrl, $options);
		$validResponseCodes = [200, 201];

		$logger = NULL;
		if ($this->getContainer())
		{
			$logger = $this->container->getLogger('kitsu-request');
			$logger->debug('Kitsu response', [
				'status' => $response->getStatus(),
				'reason' => $response->getReason(),
				'body' => $response->getBody(),
				'headers' => $response->getHeaders(),
				//'requestHeaders' => $request->getHeaders(),
			]);
		}

		if ( ! \in_array($response->getStatus(), $validResponseCodes, TRUE))
		{
			if ($logger !== NULL)
			{
				$logger->warning('Non 200 response for POST api call', (array)$response->getBody());
			}
		}

		// dump(wait($response->getBody()->buffer()));

		return Json::decode(wait($response->getBody()->buffer()));
	}
}