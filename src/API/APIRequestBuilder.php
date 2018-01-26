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

namespace Aviat\AnimeClient\API;

use function Amp\Promise\wait;

use Amp;
use Amp\Artax\{FormBody, Request};
use Aviat\Ion\Json;
use InvalidArgumentException;
use Psr\Log\LoggerAwareTrait;

/**
 * Wrapper around Artax to make it easier to build API requests
 */
class APIRequestBuilder {
	use LoggerAwareTrait;

	/**
	 * Url prefix for making url requests
	 * @var string
	 */
	protected $baseUrl = '';

	/**
	 * Url path of the request
	 * @var string
	 */
	protected $path = '';

	/**
	 * Query string for the request
	 * @var string
	 */
	protected $query = '';

	/**
	 * Default request headers
	 * @var array
	 */
	protected $defaultHeaders = [];

	/**
	 * Valid HTTP request methods
	 * @var array
	 */
	protected $validMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

	/**
	 * The current request
	 * @var \Amp\Artax\Request
	 */
	protected $request;

	/**
	 * Set an authorization header
	 *
	 * @param string $type The type of authorization, eg, basic, bearer, etc.
	 * @param string $value The authorization value
	 * @return self
	 */
	public function setAuth(string $type, string $value): self
	{
		$authString = ucfirst($type) . ' ' . $value;
		$this->setHeader('Authorization', $authString);

		return $this;
	}

	/**
	 * Set a basic authentication header
	 *
	 * @param string $username
	 * @param string $password
	 * @return self
	 */
	public function setBasicAuth(string $username, string $password): self
	{
		$this->setAuth('basic', base64_encode($username . ':' . $password));
		return $this;
	}

	/**
	 * Set the request body
	 *
	 * @param FormBody|string $body
	 * @throws \TypeError
	 * @return self
	 */
	public function setBody($body): self
	{
		$this->request = $this->request->withBody($body);
		return $this;
	}

	/**
	 * Set body as form fields
	 *
	 * @param array $fields Mapping of field names to values
	 * @throws \TypeError
	 * @return self
	 */
	public function setFormFields(array $fields): self
	{
		$body = new FormBody();
		$body->addFields($fields);

		return $this->setBody($body);
	}

	/**
	 * Unset a request header
	 *
	 * @param string $name
	 * @return self
	 */
	public function unsetHeader(string $name): self
	{
		$this->request = $this->request->withoutHeader($name);
		return $this;
	}

	/**
	 * Set a request header
	 *
	 * @param string $name
	 * @param string $value
	 * @return self
	 */
	public function setHeader(string $name, string $value = NULL): self
	{
		if (NULL === $value)
		{
			$this->unsetHeader($name);
		}
		else
		{
			$this->request = $this->request->withHeader($name, $value);
		}

		return $this;
	}

	/**
	 * Set multiple request headers
	 *
	 * name => value
	 *
	 * @param array $headers
	 * @return self
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
	 *
	 * @param mixed $body
	 * @throws \TypeError
	 * @return self
	 */
	public function setJsonBody($body): self
	{
		$requestBody = ( ! is_scalar($body))
			? Json::encode($body)
			: $body;

		return $this->setBody($requestBody);
	}

	/**
	 * Append a query string in array format
	 *
	 * @param array $params
	 * @return self
	 */
	public function setQuery(array $params): self
	{
		$this->query = http_build_query($params);
		return $this;
	}

	/**
	 * Return the promise for the current request
	 *
	 * @throws \Throwable
	 * @return \Amp\Artax\Request
	 */
	public function getFullRequest(): Request
	{
		$this->buildUri();

		if ($this->logger)
		{
			$this->logger->debug('API Request', [
				'request_url' => $this->request->getUri(),
				'request_headers' => $this->request->getHeaders(),
				'request_body' => wait(
					$this->request->getBody()
						->createBodyStream()
						->read()
				)
			]);
		}

		return $this->request;
	}

	/**
	 * Get the data from the response of the passed request
	 *
	 * @param Request $request
	 * @return mixed
	 * @throws \Error
	 * @throws \Throwable
	 * @throws \TypeError
	 */
	public function getResponseData(Request $request)
	{
		$response = wait((new HummingbirdClient)->request($request));
		return wait($response->getBody());
	}

	/**
	 * Create a new http request
	 *
	 * @param string $type
	 * @param string $uri
	 * @throws InvalidArgumentException
	 * @return self
	 */
	public function newRequest(string $type, string $uri): self
	{
		if ( ! \in_array($type, $this->validMethods, TRUE))
		{
			throw new InvalidArgumentException('Invalid HTTP method');
		}

		$realUrl = (strpos($uri, '//') !== FALSE)
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
	 *
	 * @return Request
	 */
	private function buildUri(): Request
	{
		$url = (strpos($this->path, '//') !== FALSE)
			? $this->path
			: $this->baseUrl . $this->path;

		if ( ! empty($this->query))
		{
			$url .= '?' . $this->query;
		}

		$this->request = $this->request->withUri($url);

		return $this->request;
	}

	/**
	 * Reset the class state for a new request
	 *
	 * @param string $url
	 * @param string $type
	 * @return void
	 */
	private function resetState($url, $type = 'GET')
	{
		$requestUrl = $url ?: $this->baseUrl;

		$this->path = '';
		$this->query = '';
		$this->request = (new Request($requestUrl))
			->withMethod($type);
	}
}