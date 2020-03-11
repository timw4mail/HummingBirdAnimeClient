<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.3
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Anilist;

use const Aviat\AnimeClient\USER_AGENT;

use function Amp\Promise\wait;
use function Aviat\AnimeClient\getResponse;

use Amp\Http\Client\Request;
use Amp\Http\Client\Response;

use Aviat\AnimeClient\API\Anilist;
use Aviat\Ion\Json;
use Aviat\Ion\Di\ContainerAware;

use LogicException;
use Throwable;

trait AnilistTrait {
	use ContainerAware;

	/**
	 * The request builder for the Anilist API
	 * @var AnilistRequestBuilder
	 */
	protected $requestBuilder;

	/**
	 * The base url for api requests
	 * @var string $base_url
	 */
	protected $baseUrl = Anilist::BASE_URL;

	/**
	 * HTTP headers to send with every request
	 *
	 * @var array
	 */
	protected $defaultHeaders = [
		'Accept' => 'application/json',
		'Accept-Encoding' => 'gzip',
		'Content-type' => 'application/json',
		'User-Agent' => USER_AGENT,
	];

	/**
	 * Set the request builder object
	 *
	 * @param AnilistRequestBuilder $requestBuilder
	 * @return self
	 */
	public function setRequestBuilder($requestBuilder): self
	{
		$this->requestBuilder = $requestBuilder;
		return $this;
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
		$config = $this->getContainer()->get('config');
		$anilistConfig = $config->get('anilist');

		$request = $this->requestBuilder->newRequest('POST', $url);

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
		$response = $this->getResponse(Anilist::BASE_URL, $options);
		$validResponseCodes = [200, 201];

		$logger = NULL;
		if ($this->getContainer())
		{
			$logger = $this->container->getLogger('anilist-request');
			$logger->debug('Anilist response', [
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