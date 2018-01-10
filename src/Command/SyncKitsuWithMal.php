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

use Aviat\AnimeClient\API\{
	FailedResponseException,
	JsonAPI,
	ParallelAPIRequest,
	Mapping\AnimeWatchingStatus,
	Mapping\MangaReadingStatus
};
use Aviat\AnimeClient\API\MAL\Transformer\{
	AnimeListTransformer as ALT
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
	 * Run the Kitsu <=> MAL sync script
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

		$this->sync('anime');
		$this->sync('manga');
	}

	public function sync(string $type)
	{
		$uType = ucfirst($type);

		// Do a little check to make sure you don't have immediate issues
		// if you have 0 or 1 items in a list on MAL.
		$malList = $this->malModel->getList($type);
		$malCount = 0;
		if ( ! empty($malList))
		{
			$malCount = count(array_key_exists(0, $malList)
				? $malList
				: [$malList]
			);
		}

		try
		{
			$kitsuCount = $this->kitsuModel->{"get{$uType}ListCount"}();
		}
		catch (FailedResponseException $e)
		{
			dump($e);
		}


		$this->echoBox("Number of MAL {$type} list items: {$malCount}");
		$this->echoBox("Number of Kitsu {$type} list items: {$kitsuCount}");

		$data = $this->diffLists($type);

		if ( ! empty($data['addToMAL']))
		{
			$count = count($data['addToMAL']);
			$this->echoBox("Adding {$count} missing {$type} list items to MAL");
			$this->updateMALListItems($data['addToMAL'], 'create', $type);
		}

		if ( ! empty($data['updateMAL']))
		{
			$count = count($data['updateMAL']);
			$this->echoBox("Updating {$count} outdated MAL {$type} list items");
			$this->updateMALListItems($data['updateMAL'], 'update', $type);
		}

		if ( ! empty($data['addToKitsu']))
		{
			$count = count($data['addToKitsu']);
			$this->echoBox("Adding {$count} missing {$type} list items to Kitsu");
			$this->updateKitsuListItems($data['addToKitsu'], 'create', $type);
		}

		if ( ! empty($data['updateKitsu']))
		{
			$count = count($data['updateKitsu']);
			$this->echoBox("Updating {$count} outdated Kitsu {$type} list items");
			$this->updateKitsuListItems($data['updateKitsu'], 'update', $type);
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

	public function formatMALList(string $type): array
	{
		if ($type === 'anime')
		{
			return $this->formatMALAnimeList();
		}

		if ($type === 'manga')
		{
			return $this->formatMALMangaList();
		}
	}

	public function formatMALAnimeList()
	{
		$orig = $this->malModel->getList('anime');
		$output = [];

		// Bail early on empty list
		if (empty($orig))
		{
			return [];
		}

		// Due to xml parsing differences,
		// 1 item has no wrapping array.
		// In this case, just re-create the
		// wrapper array
		if ( ! array_key_exists(0, $orig))
		{
			$orig = [$orig];
		}

		foreach($orig as $item)
		{
			$output[$item['series_animedb_id']] = [
				'id' => $item['series_animedb_id'],
				'data' => [
					'status' => AnimeWatchingStatus::MAL_TO_KITSU[$item['my_status']],
					'progress' => $item['my_watched_episodes'],
					'reconsuming' => (bool) $item['my_rewatching'],
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
		$orig = $this->malModel->getList('manga');
		$output = [];

		// Bail early on empty list
		if (empty($orig))
		{
			return [];
		}

		// Due to xml parsing differences,
		// 1 item has no wrapping array.
		// In this case, just re-create the
		// wrapper array
		if ( ! array_key_exists(0, $orig))
		{
			$orig = [$orig];
		}

		foreach($orig as $item)
		{
			$output[$item['series_mangadb_id']] = [
				'id' => $item['series_mangadb_id'],
				'data' => [
					'my_status' => $item['my_status'],
					'status' => MangaReadingStatus::MAL_TO_KITSU[$item['my_status']],
					'progress' => $item['my_read_chapters'],
					'volumes' => $item['my_read_volumes'],
					'reconsuming' => (bool) $item['my_rereadingg'],
					'rating' => $item['my_score'] / 2,
					'updatedAt' => (new \DateTime())
						->setTimestamp((int)$item['my_last_updated'])
						->format(\DateTime::W3C),
				]
			];
		}

		return $output;
	}

	public function formatKitsuList(string $type = 'anime'): array
	{
		$data = $this->kitsuModel->{'getFull' . ucfirst($type) . 'List'}();

		if (empty($data))
		{
			return [];
		}

		$includes = JsonAPI::organizeIncludes($data['included']);
		$includes['mappings'] = $this->filterMappings($includes['mappings'], $type);

		$output = [];

		foreach($data['data'] as $listItem)
		{
			$id = $listItem['relationships'][$type]['data']['id'];

			$potentialMappings = $includes[$type][$id]['relationships']['mappings'];
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

	public function diffLists(string $type = 'anime'): array
	{
		// Get libraryEntries with media.mappings from Kitsu
		// Organize mappings, and ignore entries without mappings
		$kitsuList = $this->formatKitsuList($type);

		// Get MAL list data
		$malList = $this->formatMALList($type);

		$itemsToAddToMAL = [];
		$itemsToAddToKitsu = [];
		$malUpdateItems = [];
		$kitsuUpdateItems = [];

		$malIds = array_column($malList, 'id');
		$kitsuMalIds = array_column($kitsuList, 'malId');
		$missingMalIds = array_diff($malIds, $kitsuMalIds);

		foreach($missingMalIds as $mid)
		{
			$itemsToAddToKitsu[] = array_merge($malList[$mid]['data'], [
				'id' => $this->kitsuModel->getKitsuIdFromMALId($mid, $type),
				'type' => $type
			]);
		}

		foreach($kitsuList as $kitsuItem)
		{
			if (in_array($kitsuItem['malId'], $malIds))
			{
				$item = $this->compareListItems($kitsuItem, $malList[$kitsuItem['malId']]);

				if (is_null($item))
				{
					continue;
				}

				if (in_array('kitsu', $item['updateType']))
				{
					$kitsuUpdateItems[] = $item['data'];
				}

				if (in_array('mal', $item['updateType']))
				{
					$malUpdateItems[] = $item['data'];
				}

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
			'updateMAL' => $malUpdateItems,
			'addToKitsu' => $itemsToAddToKitsu,
			'updateKitsu' => $kitsuUpdateItems
		];
	}

	public function compareListItems(array $kitsuItem, array $malItem)
	{
		$compareKeys = ['status', 'progress', 'rating', 'reconsuming'];
		$diff = [];
		$dateDiff = (new \DateTime($kitsuItem['data']['updatedAt'])) <=> (new \DateTime($malItem['data']['updatedAt']));

		foreach($compareKeys as $key)
		{
			$diff[$key] = $kitsuItem['data'][$key] <=> $malItem['data'][$key];
		}

		// No difference? Bail out early
		$diffValues = array_values($diff);
		$diffValues = array_unique($diffValues);
		if (count($diffValues) === 1 && $diffValues[0] === 0)
		{
			return;
		}

		$update = [
			'id' => $kitsuItem['id'],
			'mal_id' => $kitsuItem['malId'],
			'data' => []
		];
		$return = [
			'updateType' => []
		];

		$sameStatus = $diff['status'] === 0;
		$sameProgress = $diff['progress'] === 0;
		$sameRating = $diff['rating'] === 0;


		// If status is the same, and progress count is different, use greater progress
		if ($sameStatus && ( ! $sameProgress))
		{
			if ($diff['progress'] === 1)
			{
				$update['data']['progress'] = $kitsuItem['data']['progress'];
				$return['updateType'][] = 'mal';
			}
			else if($diff['progress'] === -1)
			{
				$update['data']['progress'] = $malItem['data']['progress'];
				$return['updateType'][] = 'kitsu';
			}
		}

		// If status and progress are different, it's a bit more complicated...
		// But, at least for now, assume newer record is correct
		if ( ! ($sameStatus || $sameProgress))
		{
			if ($dateDiff === 1)
			{
				$update['data']['status'] = $kitsuItem['data']['status'];

				if ((int)$kitsuItem['data']['progress'] !== 0)
				{
					$update['data']['progress'] = $kitsuItem['data']['progress'];
				}

				$return['updateType'][] = 'mal';
			}
			else if($dateDiff === -1)
			{
				$update['data']['status'] = $malItem['data']['status'];

				if ((int)$malItem['data']['progress'] !== 0)
				{
					$update['data']['progress'] = $kitsuItem['data']['progress'];
				}

				$return['updateType'][] = 'kitsu';
			}
		}

		// If rating is different, use the rating from the item most recently updated
		if ( ! $sameRating)
		{
			if ($dateDiff === 1)
			{
				$update['data']['rating'] = $kitsuItem['data']['rating'];
				$return['updateType'][] = 'mal';
			}
			else if ($dateDiff === -1)
			{
				$update['data']['rating'] = $malItem['data']['rating'];
				$return['updateType'][] = 'kitsu';
			}
		}

		// If status is different, use the status of the more recently updated item
		if ( ! $sameStatus)
		{
			if ($dateDiff === 1)
			{
				$update['data']['status'] = $kitsuItem['data']['status'];
				$return['updateType'][] = 'mal';
			}
			else if ($dateDiff === -1)
			{
				$update['data']['status'] = $malItem['data']['status'];
				$return['updateType'][] = 'kitsu';
			}
		}

		$return['meta'] = [
			'kitsu' => $kitsuItem['data'],
			'mal' => $malItem['data'],
			'dateDiff' => $dateDiff,
			'diff' => $diff,
		];
		$return['data'] = $update;
		$return['updateType'] = array_unique($return['updateType']);
		return $return;
	}

	public function updateKitsuListItems($itemsToUpdate, string $action = 'update', string $type = 'anime'): void
	{
		$requester = new ParallelAPIRequest();
		foreach($itemsToUpdate as $item)
		{
			if ($action === 'update')
			{
				$requester->addRequest($this->kitsuModel->updateListItem($item));
			}
			else if ($action === 'create')
			{
				$requester->addRequest($this->kitsuModel->createListItem($item));
			}
		}

		$responses = $requester->makeRequests();

		foreach($responses as $key => $response)
		{
			$responseData = Json::decode($response);

			$id = $itemsToUpdate[$key]['mal_id'];
			if ( ! array_key_exists('errors', $responseData))
			{
				$verb = ($action === 'update') ? 'updated' : 'created';
				$this->echoBox("Successfully {$verb} Kitsu {$type} list item with id: {$id}");
			}
			else
			{
				dump($responseData);
				$verb = ($action === 'update') ? 'update' : 'create';
				$this->echoBox("Failed to {$verb} Kitsu {$type} list item with id: {$id}");
			}
		}
	}

	public function updateMALListItems($itemsToUpdate, string $action = 'update', string $type = 'anime'): void
	{
		$transformer = new ALT();
		$requester = new ParallelAPIRequest();

		foreach($itemsToUpdate as $item)
		{
			if ($action === 'update')
			{
				$requester->addRequest($this->malModel->updateListItem($item, $type));
			}
			else if ($action === 'create')
			{
				$data = $transformer->untransform($item);
				$requester->addRequest($this->malModel->createFullListItem($data, $type));
			}
		}

		$responses = $requester->makeRequests();

		foreach($responses as $key => $response)
		{
			$id = $itemsToUpdate[$key]['mal_id'];
			$goodResponse = (
				($action === 'update' && $response === 'Updated') ||
				($action === 'create' && $response === 'Created')
			);
			if ($goodResponse)
			{
				$verb = ($action === 'update') ? 'updated' : 'created';
				$this->echoBox("Successfully {$verb} MAL {$type} list item with id: {$id}");
			}
			else
			{
				$verb = ($action === 'update') ? 'update' : 'create';
				$this->echoBox("Failed to {$verb} MAL {$type} list item with id: {$id}");
			}
		}
	}
}
