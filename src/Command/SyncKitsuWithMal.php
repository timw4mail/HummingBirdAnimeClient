<?php declare(strict_types=1);
/**
 * Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     AnimeListClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2017  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Command;

use Amp\Artax;
use Aviat\AnimeClient\API\Kitsu;

/**
 * Clears the API Cache
 */
class SyncKitsuWithMal extends BaseCommand {

	protected $kitsuModel;
	protected $malModel;

	/**
	 * Run the image conversion script
	 *
	 * @param array $args
	 * @param array $options
	 * @return void
	 * @throws \ConsoleKit\ConsoleException
	 */
	public function execute(array $args, array $options = [])
	{
		$this->setContainer($this->setupContainer());
		$this->setCache($this->container->get('cache'));
		$this->kitsuModel = $this->container->get('kitsu-model');
		$this->malModel = $this->container->get('mal-model');

		//$kitsuCount = $this->getKitsuAnimeListPageCount();
		//$this->echoBox("List item count: {$kitsuCount}");
		$this->MALItemCreate();

		//echo json_encode($this->getMALList(), \JSON_PRETTY_PRINT);
	}


	public function getMALList()
	{
		return $this->malModel->getFullList();
	}

	public function getKitsuAnimeListPageCount()
	{
		$cacheItem = $this->cache->getItem(Kitsu::AUTH_TOKEN_CACHE_KEY);

		$query = http_build_query([
			'filter' => [
				'user_id' => $this->kitsuModel->getUserIdByUsername(),
				'media_type' => 'Anime'
			],
			// 'include' => 'anime,anime.genres,anime.mappings,anime.streamingLinks',
			'page' => [
				'limit' => 1
			],
			'sort' => '-updated_at'
		]);
		$request = (new Artax\Request)
			->setUri("https://kitsu.io/api/edge/library-entries?{$query}")
			->setProtocol('1.1')
			->setAllHeaders([
				'Accept' => 'application/vnd.api+json',
				'Content-Type' => 'application/vnd.api+json',
				'User-Agent' => "Tim's Anime Client/4.0"
			]);

		if ($cacheItem->isHit())
		{
			$token = $cacheItem->get();
			$request->setHeader('Authorization', "bearer {$token}");
		}
		else
		{
			$this->echoBox("WARNING: NOT LOGGED IN\nSome data might be missing");
		}

		$response = \Amp\wait((new Artax\Client)->request($request));

		$body = json_decode($response->getBody(), TRUE);
		return $body['meta']['count'];
	}

	public function MALItemCreate()
	{
		$input = json_decode('{
			"watching_status": "current",
			"user_rating": "",
			"episodes_watched": "4",
			"rewatched": "0",
			"notes": "",
			"id": "15794526",
			"mal_id": "33731",
			"edit": "true"
		}', TRUE);

		$response = $this->malModel->createListItem([
			'id' => 12255,
			'status' => 'planned',
			'type' => 'anime'
		]);

		//$response = $this->malModel->updateListItem($input);
		//print_r($response);
		//echo $response->getBody();

	}

	public function diffLists()
	{
		// Get libraryEntries with media.mappings from Kitsu
		// Organize mappings, and ignore entries without mappings

		// Get MAL list data

		// Compare each list entry
			// If a list item exists only on MAL, create it on Kitsu with the existing data from MAL
			// If a list item exists only on Kitsu, create it on MAL with the existing data from Kitsu
			// If an item already exists on both APIS:
				// Compare last updated dates, and use the later one
				// Otherwise, use rewatch count, then episode progress as critera for selecting the more up
				// to date entry
				// Based on the 'newer' entry, update the other api list item
	}


}