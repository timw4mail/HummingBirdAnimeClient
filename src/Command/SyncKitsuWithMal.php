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
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Command;

use function Amp\{all, wait};

use Amp\Artax\Client;
use Aviat\AnimeClient\API\{
	JsonAPI,
	ParallelAPIRequest,
	Mapping\AnimeWatchingStatus,
	Mapping\MangaReadingStatus
};
use Aviat\AnimeClient\API\MAL\Transformer\{
	AnimeListTransformer as ALT,
	MangaListTransformer as MLT
};
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

		$this->syncAnime();
		$this->syncManga();
	}

	public function syncAnime()
	{
		$malCount = count($this->malModel->getAnimeList());
		$kitsuCount = $this->kitsuModel->getAnimeListCount();

		$this->echoBox("Number of MAL anime list items: {$malCount}");
		$this->echoBox("Number of Kitsu anime list items: {$kitsuCount}");

		$data = $this->diffAnimeLists();

		$this->echoBox("Number of anime items that need to be added to MAL: " . count($data['addToMAL']));

		if ( ! empty($data['addToMAL']))
		{
			$this->echoBox("Adding missing anime list items to MAL");
			$this->createMALAnimeListItems($data['addToMAL']);
		}

		$this->echoBox('Number of anime items that need to be added to Kitsu: ' . count($data['addToKitsu']));

		if ( ! empty($data['addToKitsu']))
		{
			$this->echoBox("Adding missing anime list items to Kitsu");
			$this->createKitusAnimeListItems($data['addToKitsu']);
		}
	}

	public function syncManga()
	{
		$malCount =  count($this->malModel->getMangaList());
		$kitsuCount = $this->kitsuModel->getMangaListCount();

		$this->echoBox("Number of MAL manga list items: {$malCount}");
		$this->echoBox("Number of Kitsu manga list items: {$kitsuCount}");

		$data = $this->diffMangaLists();

		$this->echoBox("Number of manga items that need to be added to MAL: " . count($data['addToMAL']));

		if ( ! empty($data['addToMAL']))
		{
			$this->echoBox("Adding missing manga list items to MAL");
			$this->createMALMangaListItems($data['addToMAL']);
		}

		$this->echoBox('Number of manga items that need to be added to Kitsu: ' . count($data['addToKitsu']));

		if ( ! empty($data['addToKitsu']))
		{
			$this->echoBox("Adding missing manga list items to Kitsu");
			$this->createKitsuMangaListItems($data['addToKitsu']);
		}
	}

	public function filterMappings(array $includes, string $type = 'anime'): array
	{
		$output = [];

		foreach($includes as $id => $mapping)
		{
			if ($mapping['externalSite'] === "myanimelist/{$type}")
			{
				$output[$id] = $mapping;
			}
		}

		return $output;
	}

	public function formatMALAnimeList()
	{
		$orig = $this->malModel->getAnimeList();
		$output = [];

		foreach($orig as $item)
		{
			$output[$item['series_animedb_id']] = [
				'id' => $item['series_animedb_id'],
				'data' => [
					'status' => AnimeWatchingStatus::MAL_TO_KITSU[$item['my_status']],
					'progress' => $item['my_watched_episodes'],
					'reconsuming' => (bool) $item['my_rewatching'],
					'reconsumeCount' => array_key_exists('times_rewatched', $item)
						? $item['times_rewatched']
						: 0,
					// 'notes' => ,
					'rating' => $item['my_score'] / 2,
					'updatedAt' => (new \DateTime())
						->setTimestamp((int)$item['my_last_updated'])
						->format(\DateTime::W3C),
				]
			];
		}

		return $output;
	}

	public function formatMALMangaList()
	{
		$orig = $this->malModel->getMangaList();
		$output = [];

		foreach($orig as $item)
		{
			$output[$item['series_mangadb_id']] = [
				'id' => $item['series_mangadb_id'],
				'data' => [
					'my_status' => $item['my_status'],
					'status' => MangaReadingStatus::MAL_TO_KITSU[$item['my_status']],
					'progress' => $item['my_read_chapters'],
					'reconsuming' => (bool) $item['my_rereadingg'],
					/* 'reconsumeCount' => array_key_exists('times_rewatched', $item)
						? $item['times_rewatched']
						: 0, */
					// 'notes' => ,
					'rating' => $item['my_score'] / 2,
					'updatedAt' => (new \DateTime())
						->setTimestamp((int)$item['my_last_updated'])
						->format(\DateTime::W3C),
				]
			];
		}

		return $output;
	}

	public function filterKitsuAnimeList()
	{
		$data = $this->kitsuModel->getFullAnimeList();
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

	public function filterKitsuMangaList()
	{
		$data = $this->kitsuModel->getFullMangaList();
		$includes = JsonAPI::organizeIncludes($data['included']);
		$includes['mappings'] = $this->filterMappings($includes['mappings'], 'manga');

		$output = [];

		foreach($data['data'] as $listItem)
		{
			$mangaId = $listItem['relationships']['manga']['data']['id'];
			$potentialMappings = $includes['manga'][$mangaId]['relationships']['mappings'];
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

	public function diffMangaLists()
	{
		$kitsuList = $this->filterKitsuMangaList();
		$malList = $this->formatMALMangaList();

		$itemsToAddToMAL =  [];
		$itemsToAddToKitsu = [];

		$malIds = array_column($malList, 'id');
		$kitsuMalIds = array_column($kitsuList, 'malId');
		$missingMalIds = array_diff($malIds, $kitsuMalIds);

		foreach($missingMalIds as $mid)
		{
			$itemsToAddToKitsu[] = array_merge($malList[$mid]['data'], [
				'id' => $this->kitsuModel->getKitsuIdFromMALId($mid, 'manga'),
				'type' => 'manga'
			]);
		}

		foreach($kitsuList as $kitsuItem)
		{
			if (in_array($kitsuItem['malId'], $malIds))
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

		return [
			'addToMAL' => $itemsToAddToMAL,
			'addToKitsu' => $itemsToAddToKitsu
		];
	}

	public function diffAnimeLists()
	{
		// Get libraryEntries with media.mappings from Kitsu
		// Organize mappings, and ignore entries without mappings
		$kitsuList = $this->filterKitsuAnimeList();

		// Get MAL list data
		$malList = $this->formatMALAnimeList();

		$itemsToAddToMAL = [];
		$itemsToAddToKitsu = [];

		$malIds = array_column($malList, 'id');
		$kitsuMalIds = array_column($kitsuList, 'malId');
		$missingMalIds = array_diff($malIds, $kitsuMalIds);

		foreach($missingMalIds as $mid)
		{
			// print_r($malList[$mid]);
			$itemsToAddToKitsu[] = array_merge($malList[$mid]['data'], [
				'id' => $this->kitsuModel->getKitsuIdFromMALId($mid),
				'type' => 'anime'
			]);
		}

		foreach($kitsuList as $kitsuItem)
		{
			if (in_array($kitsuItem['malId'], $malIds))
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
			'addToKitsu' => $itemsToAddToKitsu
		];
	}

	public function createKitsuMangaListItems($itemsToAdd)
	{
		$requester = new ParallelAPIRequest();
		foreach($itemsToAdd as $item)
		{
			$requester->addRequest($this->kitsuModel->createListItem($item));
		}

		$responses = $requester->makeRequests();

		foreach($responses as $key => $response)
		{
			$id = $itemsToAdd[$key]['id'];
			if ($response->getStatus() === 201)
			{
				$this->echoBox("Successfully created Kitsu manga list item with id: {$id}");
			}
			else
			{
				echo $response->getBody();
				$this->echoBox("Failed to create Kitsu manga list item with id: {$id}");
			}
		}
	}

	public function createMALMangaListItems($itemsToAdd)
	{
		$transformer = new MLT();
		$requester = new ParallelAPIRequest();

		foreach($itemsToAdd as $item)
		{
			$data = $transformer->untransform($item);
			$requester->addRequest($this->malModel->createFullListItem($data, 'manga'));
		}

		$responses = $requester->makeRequests();

		foreach($responses as $key => $response)
		{
			$id = $itemsToAdd[$key]['mal_id'];
			if ($response->getBody() === 'Created')
			{
				$this->echoBox("Successfully created MAL manga list item with id: {$id}");
			}
			else
			{
				$this->echoBox("Failed to create MAL manga list item with id: {$id}");
			}
		}
	}

	public function createKitusAnimeListItems($itemsToAdd)
	{
		$requester = new ParallelAPIRequest();
		foreach($itemsToAdd as $item)
		{
			$requester->addRequest($this->kitsuModel->createListItem($item));
		}

		$responses = $requester->makeRequests();

		foreach($responses as $key => $response)
		{
			$id = $itemsToAdd[$key]['id'];
			if ($response->getStatus() === 201)
			{
				$this->echoBox("Successfully created Kitsu anime list item with id: {$id}");
			}
			else
			{
				echo $response->getBody();
				$this->echoBox("Failed to create Kitsu anime list item with id: {$id}");
			}
		}
	}

	public function createMALAnimeListItems($itemsToAdd)
	{
		$transformer = new ALT();
		$requester = new ParallelAPIRequest();

		foreach($itemsToAdd as $item)
		{
			$data = $transformer->untransform($item);
			$requester->addRequest($this->malModel->createFullListItem($data));
		}

		$responses = $requester->makeRequests();

		foreach($responses as $key => $response)
		{
			$id = $itemsToAdd[$key]['mal_id'];
			if ($response->getBody() === 'Created')
			{
				$this->echoBox("Successfully created MAL anime list item with id: {$id}");
			}
			else
			{
				$this->echoBox("Failed to create MAL anime list item with id: {$id}");
			}
		}
	}
}