<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
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

use function Amp\call;
use function Amp\Promise\{all, wait};

/**
 * Class to simplify making and validating simultaneous requests
 */
final class ParallelAPIRequest {

	/**
	 * Set of requests to make in parallel
	 *
	 * @var array
	 */
	private $requests = [];

	/**
	 * Add a request
	 *
	 * @param string|\Amp\Artax\Request $request
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
	 * @param string[]|\Amp\Artax\Request[] $requests
	 * @return self
	 */
	public function addRequests(array $requests): self
	{
		array_walk($requests, [$this, 'addRequest']);
		return $this;
	}

	/**
	 * Actually make the requests
	 *
	 * @return array
	 */
	public function makeRequests(): array
	{
		$client = new HummingbirdClient();
		$promises = [];

		foreach ($this->requests as $key => $url)
		{
			$promises[$key] = call(function () use ($client, $url) {
				$response = yield $client->request($url);
				return yield $response->getBody();
			});
		}

		return wait(all($promises));
	}
}