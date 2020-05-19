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
	 *
	 * @var array
	 */
	private array $requests = [];

	/**
	 * Add a request
	 *
	 * @param string|Request $request
	 * @param string|number $key
	 * @return self
	 */
	public function addRequest($request, $key = NULL): self
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
	 * @return self
	 */
	public function addRequests(array $requests): self
	{
		array_walk($requests, [$this, 'addRequest']);
		return $this;
	}

	/**
	 * Make the requests, and return the body for each
	 *
	 * @return array
	 * @throws Throwable
	 */
	public function makeRequests(): array
	{
		$client = getApiClient();

		$promises = [];

		foreach ($this->requests as $key => $url)
		{
			$promises[$key] = call(static function () use ($client, $url) {
				$response = yield $client->request($url);
				return yield $response->getBody()->buffer();
			});
		}

		return wait(all($promises));
	}

	/**
	 * Make the requests and return the response objects
	 *
	 * @return array
	 * @throws Throwable
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