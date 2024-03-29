<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.1
 *
 * @copyright   2015 - 2023  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API;

use Amp\Future;
use Amp\Http\Client\{Request, Response};
use Throwable;

use function Amp\async;
use function Aviat\AnimeClient\getApiClient;

/**
 * Class to simplify making and validating simultaneous requests
 */
final class ParallelAPIRequest
{
	/**
	 * Set of requests to make in parallel
	 */
	private array $requests = [];

	/**
	 * Add a request
	 */
	public function addRequest(string|Request $request, string|int|null $key = NULL): self
	{
		if ($key !== NULL)
		{
			$this->requests[$key] = $request;

			return $this;
		}

		$this->requests[] = $request;

		return $this;
	}

	/**
	 * Add multiple requests
	 *
	 * @param Request[]|string[] $requests
	 */
	public function addRequests(array $requests): self
	{
		array_walk($requests, $this->addRequest(...));

		return $this;
	}

	/**
	 * Make the requests, and return the body for each
	 *
	 * @throws Throwable
	 */
	public function makeRequests(): array
	{
		$futures = [];

		foreach ($this->requests as $key => $url)
		{
			$futures[$key] = async(static fn () => self::bodyHandler($url));
		}

		return Future\await($futures);
	}

	/**
	 * Make the requests and return the response objects
	 *
	 * @throws Throwable
	 */
	public function getResponses(): array
	{
		$futures = [];

		foreach ($this->requests as $key => $url)
		{
			$futures[$key] = async(static fn () => self::responseHandler($url));
		}

		return Future\await($futures);
	}

	private static function bodyHandler(string|Request $uri): string
	{
		$client = getApiClient();

		if (is_string($uri))
		{
			$uri = new Request($uri);
		}
		$response = $client->request($uri);

		return $response->getBody()->buffer();
	}

	private static function responseHandler(string|Request $uri): Response
	{
		$client = getApiClient();

		if (is_string($uri))
		{
			$uri = new Request($uri);
		}

		return $client->request($uri);
	}
}
