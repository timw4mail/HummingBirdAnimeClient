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

namespace Aviat\AnimeClient\API\Anilist;

use Amp\Http\Client\{Request, Response};
use Aviat\AnimeClient\Anilist;
use Aviat\AnimeClient\API\APIRequestBuilder;
use Aviat\Ion\Di\{ContainerAware, ContainerInterface};
use Aviat\Ion\{Json, JsonException};
use LogicException;
use Throwable;

use function Amp\Promise\wait;

use function Aviat\AnimeClient\getResponse;
use function in_array;
use const Aviat\AnimeClient\USER_AGENT;

final class RequestBuilder extends APIRequestBuilder
{
	use ContainerAware;

	/**
	 * The base url for api requests
	 */
	protected string $baseUrl = Anilist::BASE_URL;

	/**
	 * Valid HTTP request methods
	 */
	protected array $validMethods = ['POST'];

	/**
	 * HTTP headers to send with every request
	 */
	protected array $defaultHeaders = [
		'Accept' => 'application/json',
		// 'Accept-Encoding' => 'gzip',
		'Content-type' => 'application/json',
		'User-Agent' => USER_AGENT,
	];

	public function __construct(ContainerInterface $container)
	{
		$this->setContainer($container);
	}

	/**
	 * Create a request object
	 * @throws Throwable
	 */
	public function setUpRequest(string $url, array $options = []): Request
	{
		$config = $this->getContainer()->get('config');
		$anilistConfig = $config->get('anilist');

		$request = $this->newRequest('POST', $url);

		// You can only authenticate the request if you
		// actually have an access_token saved
		if ($config->has(['anilist', 'access_token']))
		{
			$request = $request->setAuth('bearer', $anilistConfig['access_token']);
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
	 */
	public function runQuery(string $name, array $variables = []): array
	{
		$file = __DIR__ . "/Queries/{$name}.graphql";
		if ( ! file_exists($file))
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

		return $this->postRequest([
			'body' => $body,
		]);
	}

	/**
	 * @throws Throwable
	 */
	public function mutateRequest(string $name, array $variables = []): Request
	{
		$file = __DIR__ . "/Mutations/{$name}.graphql";
		if ( ! file_exists($file))
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

		return $this->setUpRequest(Anilist::BASE_URL, [
			'body' => $body,
		]);
	}

	/**
	 * @throws Throwable
	 * @return mixed[]
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
	 * @throws Throwable
	 */
	private function getResponse(string $url, array $options = []): Response
	{
		$logger = $this->container->getLogger('anilist-request');

		$request = $this->setUpRequest($url, $options);
		$response = getResponse($request);

		if ($logger !== NULL)
		{
			$logger->debug('Anilist response', [
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
	 * @throws Throwable
	 */
	public function getResponseFromRequest(Request $request): Response
	{
		$logger = $this->container->getLogger('anilist-request');

		$response = getResponse($request);

		if ($logger !== NULL)
		{
			$logger->debug('Anilist response', [
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
	 * Remove some boilerplate for post requests
	 *
	 * @throws Throwable
	 */
	protected function postRequest(array $options = []): array
	{
		$response = $this->getResponse(Anilist::BASE_URL, $options);
		$validResponseCodes = [200, 201];

		$logger = $this->container->getLogger('anilist-request');
		$logger?->debug('Anilist response', [
			'status' => $response->getStatus(),
			'reason' => $response->getReason(),
			'body' => $response->getBody(),
			'headers' => $response->getHeaders(),
			//'requestHeaders' => $request->getHeaders(),
		]);

		if ( ! in_array($response->getStatus(), $validResponseCodes, TRUE))
		{
			$logger?->warning('Non 200 response for POST api call', (array) $response->getBody());
		}

		$rawBody = wait($response->getBody()->buffer());

		try
		{
			return Json::decode($rawBody);
		}
		catch (JsonException $e)
		{
			dump($e);
			dump($rawBody);

			exit();
		}
	}
}
