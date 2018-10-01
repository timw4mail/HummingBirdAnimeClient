<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.1
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2018  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.1
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Command;

use Aviat\AnimeClient\API\{
	FailedResponseException,
	JsonAPI,
	Kitsu\Transformer\MangaListTransformer,
	ParallelAPIRequest
};
use Aviat\AnimeClient\API\Anilist\Transformer\{
	AnimeListTransformer as AALT,
	MangaListTransformer as AMLT
};
use Aviat\AnimeClient\API\Mapping\{AnimeWatchingStatus, MangaReadingStatus};
use Aviat\AnimeClient\Types\FormItem;
use Aviat\Ion\Json;
use DateTime;

/**
 * Syncs list data between Anilist and Kitsu
 */
final class SyncLists extends BaseCommand {

	/**
	 * Model for making requests to Anilist API
	 * @var \Aviat\AnimeClient\API\Anilist\Model
	 */
	protected $anilistModel;

	/**
	 * Model for making requests to Kitsu API
	 * @var \Aviat\AnimeClient\API\Kitsu\Model
	 */
	protected $kitsuModel;

	/**
	 * Run the Kitsu <=> Anilist sync script
	 *
	 * @param array $args
	 * @param array $options
	 * @throws \Aviat\Ion\Di\Exception\ContainerException
	 * @throws \Aviat\Ion\Di\Exception\NotFoundException
	 * @throws \Throwable
	 */
	public function execute(array $args, array $options = []): void
	{
		$this->setContainer($this->setupContainer());
		$this->setCache($this->container->get('cache'));
		$this->anilistModel = $this->container->get('anilist-model');
		$this->kitsuModel = $this->container->get('kitsu-model');

		$this->sync('anime');
		$this->sync('manga');

		$this->echoBox('Finished syncing lists');
	}

	/**
	 * Attempt to synchronize external APIs
	 *
	 * @param string $type
	 * @throws \Throwable
	 */
	protected function sync(string $type): void
	{
		$uType = ucfirst($type);

		$kitsuCount = 0;
		try
		{
			$kitsuCount = $this->kitsuModel->{"get{$uType}ListCount"}();
		}
		catch (FailedResponseException $e)
		{
			dump($e);
		}


		$this->echoBox("Number of Kitsu {$type} list items: {$kitsuCount}");

		$data = $this->diffLists($type);

		if ( ! empty($data['addToAnilist']))
		{
			$count = count($data['addToAnilist']);
			$this->echoBox("Adding {$count} missing {$type} list items to Anilist");
			$this->updateAnilistListItems($data['addToAnilist'], 'create', $type);
		}

		if ( ! empty($data['updateAnilist']))
		{
			$count = count($data['updateAnilist']);
			$this->echoBox("Updating {$count} outdated Anilist {$type} list items");
			$this->updateAnilistListItems($data['updateAnilist'], 'update', $type);
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

	/**
	 * Filter Kitsu mappings for the specified type
	 *
	 * @param array $includes
	 * @param string $type
	 * @return array
	 */
	protected function filterMappings(array $includes, string $type = 'anime'): array
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

	/**
	 * Format an Anilist list for comparison
	 *
	 * @param string $type
	 * @return array
	 */
	protected function formatAnilistList(string $type): array
	{
		$type = ucfirst($type);
		$method = "formatAnilist{$type}List";
		return $this->$method();
	}

	/**
	 * Format an Anilist anime list for comparison
	 *
	 * @return array
	 * @throws \Aviat\Ion\Di\Exception\ContainerException
	 * @throws \Aviat\Ion\Di\Exception\NotFoundException
	 */
	protected function formatAnilistAnimeList(): array
	{
		$anilistList = $this->anilistModel->getSyncList('ANIME');
		$anilistTransformer = new AALT();

		$transformedAnilist = [];

		foreach ($anilistList['data']['MediaListCollection']['lists'] as $list)
		{
			$newTransformed = $anilistTransformer->untransformCollection($list['entries']);
			$transformedAnilist = array_merge($transformedAnilist, $newTransformed);
		}

		// Key the array by the mal_id for easier reference in the next comparision step
		$output = [];
		foreach ($transformedAnilist as $item)
		{
			$output[$item['mal_id']] = $item->toArray();
		}

		$count = count($output);
		$this->echoBox("Number of Anilist anime list items: {$count}");

		return $output;
	}

	/**
	 * Format an Anilist manga list for comparison
	 *
	 * @return array
	 * @throws \Aviat\Ion\Di\Exception\ContainerException
	 * @throws \Aviat\Ion\Di\Exception\NotFoundException
	 */
	protected function formatAnilistMangaList(): array
	{
		$anilistList = $this->anilistModel->getSyncList('MANGA');
		$anilistTransformer = new AMLT();

		$transformedAnilist = [];

		foreach ($anilistList['data']['MediaListCollection']['lists'] as $list)
		{
			$newTransformed = $anilistTransformer->untransformCollection($list['entries']);
			$transformedAnilist = array_merge($transformedAnilist, $newTransformed);
		}

		// Key the array by the mal_id for easier reference in the next comparision step
		$output = [];
		foreach ($transformedAnilist as $item)
		{
			$output[$item['mal_id']] = $item->toArray();
		}

		$count = count($output);
		$this->echoBox("Number of Anilist manga list items: {$count}");

		return $output;
	}

	/**
	 * Format a kitsu list for the sake of comparision
	 *
	 * @param string $type
	 * @return array
	 */
	protected function formatKitsuList(string $type = 'anime'): array
	{
		$method = 'getFullRaw' . ucfirst($type) . 'List';
		$data = $this->kitsuModel->$method();

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

			// Skip to the next item if there isn't a Anilist ID
			if ($malId === NULL)
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

	/**
	 * Go through lists of the specified type, and determine what kind of action each item needs
	 *
	 * @param string $type
	 * @return array
	 */
	protected function diffLists(string $type = 'anime'): array
	{
		// Get libraryEntries with media.mappings from Kitsu
		// Organize mappings, and ignore entries without mappings
		$kitsuList = $this->formatKitsuList($type);

		// Get Anilist list data
		$anilistList = $this->formatAnilistList($type);

		$itemsToAddToAnilist = [];
		$itemsToAddToKitsu = [];
		$anilistUpdateItems = [];
		$kitsuUpdateItems = [];

		$malBlackList = ($type === 'anime')
			? [
				27821, // Fate/stay night: Unlimited Blade Works - Prologue
				29317, // Saekano: How to Raise a Boring Girlfriend Prologue
				30514, // Nisekoinogatari
			] : [
				114638, // Cells at Work: Black
			];

		$malIds = array_keys($anilistList);
		$kitsuMalIds = array_map('intval', array_column($kitsuList, 'malId'));
		$missingMalIds = array_diff($malIds, $kitsuMalIds);
		$missingMalIds = array_diff($missingMalIds, $malBlackList);

		foreach($missingMalIds as $mid)
		{
			$itemsToAddToKitsu[] = array_merge($anilistList[$mid]['data'], [
				'id' => $this->kitsuModel->getKitsuIdFromMALId((string)$mid, $type),
				'type' => $type
			]);
		}

		foreach($kitsuList as $kitsuItem)
		{
			$malId = $kitsuItem['malId'];

			if (\in_array((int)$malId, $malBlackList, TRUE))
			{
				continue;
			}

			if (array_key_exists($malId, $anilistList))
			{
				$anilistItem = $anilistList[$malId];
				// dump($anilistItem);

				$item = $this->compareListItems($kitsuItem, $anilistItem);

				if ($item === NULL)
				{
					continue;
				}

				if (\in_array('kitsu', $item['updateType'], TRUE))
				{
					$kitsuUpdateItems[] = $item['data'];
				}

				if (\in_array('anilist', $item['updateType'], TRUE))
				{
					$anilistUpdateItems[] = $item['data'];
				}

				continue;
			}

			$statusMap = ($type === 'anime') ? AnimeWatchingStatus::class : MangaReadingStatus::class;

			// Looks like this item only exists on Kitsu
			$kItem = $kitsuItem['data'];
			$newItemStatus = ($kItem['reconsuming'] === true) ? 'REPEATING' : $statusMap::KITSU_TO_ANILIST[$kItem['status']];
			$itemsToAddToAnilist[] = [
				'mal_id' => $malId,
				'data' => [
					'notes' => $kItem['notes'],
					'private' => $kItem['private'],
					'progress' => $kItem['progress'],
					'repeat' => $kItem['reconsumeCount'],
					'score' => $kItem['ratingTwenty'] * 5, // 100 point score on Anilist
					'status' => $newItemStatus,
				],
			];
		}

		return [
			'addToAnilist' => $itemsToAddToAnilist,
			'updateAnilist' => $anilistUpdateItems,
			'addToKitsu' => $itemsToAddToKitsu,
			'updateKitsu' => $kitsuUpdateItems
		];
	}

	/**
	 * Compare two list items, and return the out of date one, if one exists
	 *
	 * @param array $kitsuItem
	 * @param array $anilistItem
	 * @return array|null
	 */
	protected function compareListItems(array $kitsuItem, array $anilistItem): ?array
	{
		$compareKeys = [
			'notes',
			'progress',
			'rating',
			'reconsumeCount',
			'reconsuming',
			'status',
		];
		$diff = [];
		$dateDiff = new DateTime($kitsuItem['data']['updatedAt']) <=> new DateTime((string)$anilistItem['data']['updatedAt']);

		// Correct differences in notation
		$kitsuItem['data']['rating'] = $kitsuItem['data']['ratingTwenty'] / 2;

		foreach($compareKeys as $key)
		{
			$diff[$key] = $kitsuItem['data'][$key] <=> $anilistItem['data'][$key];
		}

		// No difference? Bail out early
		$diffValues = array_values($diff);
		$diffValues = array_unique($diffValues);
		if (count($diffValues) === 1 && $diffValues[0] === 0)
		{
			return NULL;
		}

		$update = [
			'id' => $kitsuItem['id'],
			'mal_id' => $kitsuItem['malId'],
			'data' => []
		];
		$return = [
			'updateType' => []
		];

		$sameNotes = $diff['notes'] === 0;
		$sameStatus = $diff['status'] === 0;
		$sameProgress = $diff['progress'] === 0;
		$sameRating = $diff['ratingTwenty'] === 0;
		$sameRewatchCount = $diff['reconsumeCount'] === 0;

		// If an item is completed, make sure the 'reconsuming' flag is false
		if ($kitsuItem['data']['status'] === 'completed' && $kitsuItem['data']['reconsuming'] === TRUE)
		{
			$update['data']['reconsuming'] = FALSE;
			$return['updateType'][] = 'kitsu';
		}

		// If status is the same, and progress count is different, use greater progress
		if ($sameStatus && ( ! $sameProgress))
		{
			if ($diff['progress'] === 1)
			{
				$update['data']['progress'] = $kitsuItem['data']['progress'];
				$return['updateType'][] = 'anilist';
			}
			else if($diff['progress'] === -1)
			{
				$update['data']['progress'] = $anilistItem['data']['progress'];
				$return['updateType'][] = 'kitsu';
			}
		}

		// If status is different, use the status of the more recently updated item
		if ( ! $sameStatus)
		{
			if ($dateDiff === 1)
			{
				$update['data']['status'] = $kitsuItem['data']['status'];
				$return['updateType'][] = 'anilist';
			} else if ($dateDiff === -1)
			{
				$update['data']['status'] = $anilistItem['data']['status'];
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

				$return['updateType'][] = 'anilist';
			}
			else if($dateDiff === -1)
			{
				$update['data']['status'] = $anilistItem['data']['status'];

				if ((int)$anilistItem['data']['progress'] !== 0)
				{
					$update['data']['progress'] = $kitsuItem['data']['progress'];
				}

				$return['updateType'][] = 'kitsu';
			}
		}

		// Use the first set rating, otherwise use the newer rating
		if ( ! $sameRating)
		{
			if ($kitsuItem['data']['rating'] !== 0 && $dateDiff === 1)
			{
				$update['data']['ratingTwenty'] = $kitsuItem['data']['ratingTwenty'];
				$return['updateType'][] = 'anilist';
			}
			else if($dateDiff === -1)
			{
				$update['data']['ratingTwenty'] = $anilistItem['data']['rating'] * 2;
				$return['updateType'][] = 'kitsu';
			}
		}

		// If notes are set, use kitsu, otherwise, set kitsu from anilist
		if ( ! $sameNotes)
		{
			if ($kitsuItem['data']['notes'] !== '')
			{
				$update['data']['notes'] = $kitsuItem['data']['notes'];
				$return['updateType'][] = 'anilist';
			}
			else
			{
				$update['data']['notes'] = $anilistItem['data']['notes'];
				$return['updateType'][] = 'kitsu';
			}
		}

		// Assume the larger reconsumeCount is correct
		if ( ! $sameRewatchCount)
		{
			if ($diff['reconsumeCount'] === 1)
			{
				$update['data']['reconsumeCount'] = $kitsuItem['data']['reconsumeCount'];
				$return['updateType'][] = 'anilist';
			}
			else if ($diff['reconsumeCount'] === -1)
			{
				$update['data']['reconsumeCount'] = $anilistItem['data']['reconsumeCount'];
				$return['updateType'][] = 'kitsu';
			}
		}



		$return['meta'] = [
			'kitsu' => $kitsuItem['data'],
			'anilist' => $anilistItem['data'],
			'dateDiff' => $dateDiff,
			'diff' => $diff,
		];
		$return['data'] = $update;
		$return['updateType'] = array_unique($return['updateType']);

		// Fill in missing data values for update on Anlist
		// so I don't have to create a really complex graphql query
		// to handle each combination of fields
		if ($return['updateType'][0] === 'anilist')
		{
			$prevData = [
				'notes' => $kitsuItem['data']['notes'],
				'private' => $kitsuItem['data']['private'],
				'progress' => $kitsuItem['data']['progress'],
				'rating' => $kitsuItem['data']['ratingTwenty'] * 5,
				'reconsumeCount' => $kitsuItem['data']['reconsumeCount'],
				'reconsuming' => $kitsuItem['data']['reconsuming'],
				'status' => $kitsuItem['data']['status'],
			];

			$return['data']['data'] = array_merge($prevData, $return['data']['data']);
		}

		dump($return);

		return $return;
	}

	/**
	 * Create/Update list items on Kitsu
	 *
	 * @param array $itemsToUpdate
	 * @param string $action
	 * @param string $type
	 * @throws \Throwable
	 */
	protected function updateKitsuListItems(array $itemsToUpdate, string $action = 'update', string $type = 'anime'): void
	{
		$requester = new ParallelAPIRequest();
		foreach($itemsToUpdate as $item)
		{
			if ($action === 'update')
			{
				$requester->addRequest(
					$this->kitsuModel->updateListItem(new FormItem($item))
				);
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

			$id = $itemsToUpdate[$key]['id'];
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

	/**
	 * Create/Update list items on Anilist
	 *
	 * @param array $itemsToUpdate
	 * @param string $action
	 * @param string $type
	 * @throws \Throwable
	 */
	protected function updateAnilistListItems(array $itemsToUpdate, string $action = 'update', string $type = 'anime'): void
	{
		$requester = new ParallelAPIRequest();

		foreach($itemsToUpdate as $item)
		{
			if ($action === 'update')
			{
				$requester->addRequest(
					$this->anilistModel->updateListItem(new FormItem($item), $type)
				);
			}
			else if ($action === 'create')
			{
				$requester->addRequest($this->anilistModel->createFullListItem($item, $type));
			}
		}

		$responses = $requester->makeRequests();

		foreach($responses as $key => $response)
		{
			$id = $itemsToUpdate[$key]['mal_id'];

			$responseData = Json::decode($response);

			// $id = $itemsToUpdate[$key]['id'];
			if ( ! array_key_exists('errors', $responseData))
			{
				$verb = ($action === 'update') ? 'updated' : 'created';
				$this->echoBox("Successfully {$verb} Anilist {$type} list item with id: {$id}");
			}
			else
			{
				dump($responseData);
				$verb = ($action === 'update') ? 'update' : 'create';
				$this->echoBox("Failed to {$verb} Anilist {$type} list item with id: {$id}");
			}
		}
	}
}
