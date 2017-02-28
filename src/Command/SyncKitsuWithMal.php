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

namespace Aviat\AnimeClient\Command;

use function Amp\{all, wait};

use Amp\Artax;
use Amp\Artax\Client;
use Aviat\AnimeClient\API\{JsonAPI, Kitsu, MAL};
use Aviat\AnimeClient\API\MAL\Transformer\AnimeListTransformer as ALT;
use Aviat\Ion\Json;

/**
 * Clears the API Cache
 */
class SyncKitsuWithMal extends BaseCommand {

	/**
	 * Model for making requests to Kitsu API
	 * @var \Aviat\AnimeClient\API\Kitsu\Model
	 */
	protected $kitsuModel;
	
	/**
	 * Model for making requests to MAL API
	 * @var \Aviat\AnimeClient\API\MAL\Model
	 */
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
		
		$malCount = count($this->getMALList());
		$kitsuCount = $this->getKitsuAnimeListPageCount();
		
		$this->echoBox("Number of MAL list items: {$malCount}");
		$this->echoBox("Number of Kitsu list items: {$kitsuCount}");
		
		$data = $this->diffLists();
		$this->echoBox("Number of items that need to be added to MAL: " . count($data));
		
		if ( ! empty($data['addToMAL']))
		{
			$this->echoBox("Adding missing list items to MAL");
			$this->createMALListItems($data['addToMAL']);
		}
	}
	
	public function getKitsuList()
	{
		$count = $this->getKitsuAnimeListPageCount();
		$size = 100;
		$pages = ceil($count / $size);
		
		$requests = [];
		
		// Set up requests
		for ($i = 0; $i < $count; $i++)
		{
			$offset = $i * $size;
			$requests[] = $this->kitsuModel->getFullAnimeList($size, $offset);
		}
		
		$promiseArray = (new Client())->requestMulti($requests);

		$responses = wait(all($promiseArray));
		$output = [];

		foreach($responses as $response)
		{
			$data = Json::decode($response->getBody());
			$output = array_merge_recursive($output, $data);
		}

		return $output;
	}

	public function getMALList()
	{
		return $this->malModel->getFullList();
	}
	
	public function filterMappings(array $includes): array
	{
		$output = [];
		
		foreach($includes as $id => $mapping)
		{
			if ($mapping['externalSite'] === 'myanimelist/anime')
			{
				$output[$id] = $mapping;
			}
		}
		
		return $output;
	}
	
	public function formatMALList()
	{
		$orig = $this->getMALList();
		$output = [];
		
		foreach($orig as $item)
		{
			$output[$item['series_animedb_id']] = [
				'id' => $item['series_animedb_id'],
				'data' => [
					'status' => MAL::MAL_KITSU_WATCHING_STATUS_MAP[$item['my_status']],
					'progress' => $item['my_watched_episodes'],
					'reconsuming' => (bool) $item['my_rewatching'],
					'reconsumeCount' => array_key_exists('times_rewatched', $item)
						? $item['times_rewatched']
						: 0,
					// 'notes' => ,
					'rating' => $item['my_score'],
					'updatedAt' => (new \DateTime())
						->setTimestamp((int)$item['my_last_updated'])
						->format(\DateTime::W3C),
				]
			];
		}
		
		return $output;
	}
	
	public function filterKitsuList()
	{
		$data = $this->getKitsuList();
		$includes = JsonAPI::organizeIncludes($data['included']);
		$includes['mappings'] = $this->filterMappings($includes['mappings']);
		
		$output = [];
		
		foreach($data['data'] as $listItem)
		{
			$animeId = $listItem['relationships']['anime']['data']['id'];
			$potentialMappings = $includes['anime'][$animeId]['relationships']['mappings'];
			$malId = NULL;
			
			foreach ($potentialMappings as $mappingId)
			{
				if (array_key_exists($mappingId, $includes['mappings']))
				{
					$malId = $includes['mappings'][$mappingId]['externalId'];
				}
			}
			
			// Skip to the next item if there isn't a MAL ID
			if (is_null($malId))
			{
				continue;
			}
			
			$output[$listItem['id']] = [
				'id' => $listItem['id'],
				'malId' => $malId,
				'data' => $listItem['attributes'],
			];
		}
		
		return $output;
	}

	public function getKitsuAnimeListPageCount()
	{
		return $this->kitsuModel->getAnimeListCount();
	}

	public function diffLists()
	{
		// Get libraryEntries with media.mappings from Kitsu
		// Organize mappings, and ignore entries without mappings
		$kitsuList = $this->filterKitsuList();

		// Get MAL list data
		$malList = $this->formatMALList();
		
		$itemsToAddToMAL = [];
		
		foreach($kitsuList as $kitsuItem)
		{
			if (array_key_exists($kitsuItem['malId'], $malList))
			{
				// Eventually, compare the list entries, and determine which 
				// needs to be updated
				continue;
			}
			
			// Looks like this item only exists on Kitsu
			$itemsToAddToMAL[] = [
				'mal_id' => $kitsuItem['malId'],
				'data' => $kitsuItem['data']
			];
			
		}

		// Compare each list entry
			// If a list item exists only on MAL, create it on Kitsu with the existing data from MAL
			// If a list item exists only on Kitsu, create it on MAL with the existing data from Kitsu
			// If an item already exists on both APIS:
				// Compare last updated dates, and use the later one
				// Otherwise, use rewatch count, then episode progress as critera for selecting the more up
				// to date entry
				// Based on the 'newer' entry, update the other api list item
		
		return [
			'addToMAL' => $itemsToAddToMAL,
		];
	}

	public function createMALListItems($itemsToAdd)
	{
		$transformer = new ALT();
		$requests = [];
		
		foreach($itemsToAdd as $item)
		{
			$data = $transformer->untransform($item);
			$requests[] = $this->malModel->createFullListItem($data);
		}

		$promiseArray = (new Client())->requestMulti($requests);

		$responses = wait(all($promiseArray));

		foreach($responses as $key => $response)
		{
			$id = $itemsToAdd[$key]['mal_id'];
			if ($response->getBody() === 'Created')
			{
				$this->echoBox("Successfully create list item with id: {$id}");
			}
			else
			{
				$this->echoBox("Failed to create list item with id: {$id}");
			}
		}
	}

}