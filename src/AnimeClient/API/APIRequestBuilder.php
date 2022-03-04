<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshome.page>
 * @copyright   2015 - 2022  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API;

use Amp\Http\Client\Body\FormBody;

use Amp\Http\Client\Request;
use Aviat\Ion\Json;

use Error;
use InvalidArgumentException;
use Psr\Log\LoggerAwareTrait;
use Throwable;
use TypeError;
use function Amp\Promise\wait;
use function Aviat\AnimeClient\getResponse;
use const Aviat\AnimeClient\USER_AGENT;

/**
 * Wrapper around Http\Client to make it easier to build API requests
 */
abstract class APIRequestBuilder
{
	use LoggerAwareTrait;

	/**
	 * Where to look for GraphQL request files
	 */
	protected string $filePath = '';

	/**
	 * Url prefix for making url requests
	 */
	protected string $baseUrl = '';

	/**
	 * Url path of the request
	 */
	protected string $path = '';

	/**
	 * Query string for the request
	 */
	protected string $query = '';

	/**
	 * Default request headers
	 */
	protected array $defaultHeaders = [];

	/**
	 * Valid HTTP request methods
	 */
	protected array $validMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

	/**
	 * The current request
	 */
	protected Request $request;

	/**
	 * Do a basic minimal GET request
	 */
	public static function simpleRequest(string $uri): Request
	{
		$request = (new Request($uri));
		$request->setHeader('User-Agent', USER_AGENT);
		$request->setTcpConnectTimeout(300000);
		$request->setTransferTimeout(300000);

		return $request;
	}

	/**
	 * Set an authorization header
	 *
	 * @param string $type The type of authorization, eg, basic, bearer, etc.
	 * @param string $value The authorization value
	 */
	public function setAuth(string $type, string $value): self
	{
		$authString = ucfirst($type) . ' ' . $value;
		$this->setHeader('Authorization', $authString);

		return $this;
	}

	/**
	 * Set a basic authentication header
	 */
	public function setBasicAuth(string $username, string $password): self
	{
		$this->setAuth('basic', base64_encode($username . ':' . $password));

		return $this;
	}

	/**
	 * Set the request body
	 */
	public function setBody(FormBody|string $body): self
	{
		$this->request->setBody($body);

		return $this;
	}

	/**
	 * Set body as form fields
	 *
	 * @param array $fields Mapping of field names to values
	 */
	public function setFormFields(array $fields): self
	{
		$body = new FormBody();
		$body->addFields($fields);

		return $this->setBody($body);
	}

	/**
	 * Unset a request header
	 */
	public function unsetHeader(string $name): self
	{
		$this->request->removeHeader($name);

		return $this;
	}

	/**
	 * Set a request header
	 */
	public function setHeader(string $name, ?string $value = NULL): self
	{
		if (NULL === $value)
		{
			$this->unsetHeader($name);
		}
		else
		{
			$this->request->setHeader($name, $value);
		}

		return $this;
	}

	/**
	 * Set multiple request headers
	 *
	 * name => value
	 */
	public function setHeaders(array $headers): self
	{
		foreach ($headers as $name => $value)
		{
			$this->setHeader($name, $value);
		}

		return $this;
	}

	/**
	 * Set the request body
	 */
	public function setJsonBody(mixed $body): self
	{
		$requestBody = (is_string($body))
			? $body
			: Json::encode($body);

		return $this->setBody($requestBody);
	}

	/**
	 * Append a query string in array format
	 */
	public function setQuery(array $params): self
	{
		$this->query = http_build_query($params);

		return $this;
	}

	/**
	 * Return the promise for the current request
	 *
	 * @throws Throwable
	 */
	public function getFullRequest(): Request
	{
		$this->buildUri();

		if ($this->logger !== NULL)
		{
			$this->logger->debug('API Request', [
				'request_url' => $this->request->getUri(),
				'request_headers' => $this->request->getHeaders(),
				'request_body' => wait(
					$this->request->getBody()
						->createBodyStream()
						->read()
				),
			]);
		}

		return $this->request;
	}

	/**
	 * Get the data from the response of the passed request
	 *
	 * @throws Error
	 * @throws Throwable
	 * @throws TypeError
	 * @return mixed
	 */
	public function getResponseData(Request $request)
	{
		$response = getResponse($request);

		return wait($response->getBody()->buffer());
	}

	/**
	 * Create a new http request
	 *
	 * @throws InvalidArgumentException
	 */
	public function newRequest(string $type, string $uri): self
	{
		if ( ! in_array($type, $this->validMethods, TRUE))
		{
			throw new InvalidArgumentException('Invalid HTTP method');
		}

		$realUrl = (str_contains($uri, '//'))
			? $uri
			: $this->baseUrl . $uri;

		$this->resetState($realUrl, $type);
		$this->path = $uri;

		// Actually create the full url!
		$this->buildUri();

		if ( ! empty($this->defaultHeaders))
		{
			$this->setHeaders($this->defaultHeaders);
		}

		return $this;
	}

	/**
	 * Create the full request url
	 */
	private function buildUri(): Request
	{
		$url = (str_contains($this->path, '//'))
			? $this->path
			: $this->baseUrl . $this->path;

		if ( ! empty($this->query))
		{
			$url .= '?' . $this->query;
		}

		$this->request->setUri($url);

		return $this->request;
	}

	/**
	 * Reset the class state for a new request
	 */
	private function resetState(?string $url, string $type = 'GET'): void
	{
		$requestUrl = $url ?: $this->baseUrl;

		$this->path = '';
		$this->query = '';
		$this->request = new Request($requestUrl, $type);
		$this->request->setInactivityTimeout(300000);
		$this->request->setTlsHandshakeTimeout(300000);
		$this->request->setTcpConnectTimeout(300000);
		$this->request->setTransferTimeout(300000);
	}
}
