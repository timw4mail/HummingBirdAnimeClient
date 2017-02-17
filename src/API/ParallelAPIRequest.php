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

use function Amp\{all, some, wait};

use Amp\Artax\Client;

/**
 * Class to simplify making and validating simultaneous requests
 */
class ParallelAPIRequest {
	
	/**
	 * Set of requests to make in parallel
	 *
	 * @var array
	 */
	protected $requests = [];
	
	/**
	 * Add a request
	 *
	 * @param string|Request $request
	 * @param string|number $key
	 * @return self
	 */
	public function addRequest($request, $key = NULL): self
	{
		if ( ! is_null($key))
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
	 * Actually make the requests
	 *
	 * @param bool $allowFailingRequests
	 * @return array 
	 */
	public function makeRequests(bool $allowFailingRequests = FALSE): array
	{
		$client = new Client();
		$promises = $client->requestMulti($this->requests);
		
		$func = ($allowFailingRequests) ? 'some' : 'all';
		
		$results = wait($func($promises));
		
		return $results;
	}
}