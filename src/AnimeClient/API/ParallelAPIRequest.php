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

use Amp\Http\Client\{HttpException, Request};
use Generator;
use Throwable;
use function Amp\call;

// use function Amp\Future\{async, await};
use function Amp\Promise\{all, wait};
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
		return $this->makeRequestOld();
	}

	/**
	 * Make the requests and return the response objects
	 *
	 * @throws Throwable
	 */
	public function getResponses(): array
	{
		return $this->getResponsesOld();
	}

	private function makeRequestOld(): array
	{
		$client = getApiClient();

		$promises = [];

		foreach ($this->requests as $key => $url)
		{
			$promises[$key] = call(static function () use ($client, $url): Generator {
				$response = yield $client->request($url);
				return yield $response->getBody()->buffer();
			});
		}

		return wait(all($promises));
	}

	private function makeRequestsNew(): array
	{
		$futures = [];

		foreach ($this->requests as $key => $url)
		{
			$futures[$key] = async(static fn () => self::bodyHandler($url));
		}

		return await($futures);
	}

	private function getResponsesOld(): array
	{
		$client = getApiClient();

		$promises = [];

		foreach ($this->requests as $key => $url)
		{
			$promises[$key] = call(static fn () => yield $client->request($url));
		}

		return wait(all($promises));
	}

	private function getResponsesNew(): array
	{
		$futures = [];

		foreach ($this->requests as $key => $url)
		{
			$futures[$key] = async(static fn () => self::responseHandler($url));
		}

		return await($futures);
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

	private static function responseHandler(string|Request $uri)
	{
		$client = getApiClient();

		if (is_string($uri))
		{
			$uri = new Request($uri);
		}

		return $client->request($uri);
	}
}
