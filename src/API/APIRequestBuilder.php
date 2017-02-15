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
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API;

use Amp;
use Amp\Artax\{
	Client,
	FormBody,
	Request
};
use Aviat\Ion\Di\ContainerAware;
use Aviat\Ion\Json;
use InvalidArgumentException;
use Psr\Log\LoggerAwareTrait;

/**
 * Wrapper around Artex to make it easier to build API requests
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
	 * Valid HTTP request methos
	 * @var array
	 */
	protected $validMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

	/**
	 * The current request
	 * @var \Amp\Promise
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
	 * @return self
	 */
	public function setBody($body): self
	{
		$this->request->setBody($body);
		return $this;
	}

	/**
	 * Set body as form fields
	 *
	 * @param array $fields Mapping of field names to values
	 * @return self
	 */
	public function setFormFields(array $fields): self
	{
		$this->setHeader("Content-Type", "application/x-www-form-urlencoded");
		$body = (new FormBody)->addFields($fields);
		$this->setBody($body);
		return $this;
	}

	/**
	 * Set a request header
	 *
	 * @param string $name
	 * @param string $value
	 * @return self
	 */
	public function setHeader(string $name, string $value): self
	{
		$this->request->setHeader($name, $value);
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
	 * @return \Amp\Promise
	 */
	public function getFullRequest()
	{
		$this->buildUri();

		if ($this->logger)
		{
			$this->logger->debug('API Request', [
				'request_url' => $this->request->getUri(),
				'request_headers' => $this->request->getAllHeaders(),
				'request_body' => $this->request->getBody()
			]);
		}

		return $this->request;
	}

	/**
	 * Create a new http request
	 *
	 * @param string $type
	 * @param string $uri
	 * @return self
	 */
	public function newRequest(string $type, string $uri): self
	{
		if ( ! in_array($type, $this->validMethods))
		{
			throw new InvalidArgumentException('Invalid HTTP methods');
		}

		$this->resetState();

		$this->request
			->setMethod($type)
			->setProtocol('1.1');

		$this->path = $uri;

		if ( ! empty($this->defaultHeaders))
		{
			$this->setHeaders($this->defaultHeaders);
		}

		return $this;
	}

	/**
	 * Create the full request url
	 *
	 * @return void
	 */
	private function buildUri()
	{
		$url = (strpos($this->path, '//') !== FALSE)
			? $this->path
			: $this->baseUrl . $this->path;

		if ( ! empty($this->query))
		{
			$url .= '?' . $this->query;
		}

		$this->request->setUri($url);
	}

	/**
	 * Reset the class state for a new request
	 *
	 * @return void
	 */
	private function resetState()
	{
		$this->path = '';
		$this->query = '';
		$this->request = new Request();
	}
}