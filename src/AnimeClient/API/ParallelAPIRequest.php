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

namespace Aviat\AnimeClient\API;

use Amp\Http\Client\Request;
use function Amp\call;
use function Amp\Promise\{all, wait};
use function Aviat\AnimeClient\getApiClient;

use Throwable;

/**
 * Class to simplify making and validating simultaneous requests
 */
final class ParallelAPIRequest {

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
	 * @param string[]|Request[] $requests
	 */
	public function addRequests(array $requests): self
	{
		array_walk($requests, [$this, 'addRequest']);
		return $this;
	}

	/**
	 * Make the requests, and return the body for each
	 *
	 * @throws Throwable
	 * @return mixed[]
	 */
	public function makeRequests(): array
	{
		$client = getApiClient();

		$promises = [];

		foreach ($this->requests as $key => $url)
		{
			$promises[$key] = call(static function () use ($client, $url): \Generator {
				$response = yield $client->request($url);
				return yield $response->getBody()->buffer();
			});
		}

		return wait(all($promises));
	}

	/**
	 * Make the requests and return the response objects
	 *
	 * @throws Throwable
	 * @return mixed[]
	 */
	public function getResponses(): array
	{
		$client = getApiClient();

		$promises = [];

		foreach ($this->requests as $key => $url)
		{
			$promises[$key] = call(fn () => yield $client->request($url));
		}

		return wait(all($promises));
	}
}