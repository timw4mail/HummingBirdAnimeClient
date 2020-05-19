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

namespace Aviat\AnimeClient\Command;

use ConsoleKit\Widgets;

use Aviat\AnimeClient\API\{
	Anilist\MissingIdException,
	FailedResponseException,
	JsonAPI,
	ParallelAPIRequest
};
use Aviat\AnimeClient\API\Anilist;
use Aviat\AnimeClient\API\Kitsu;
use Aviat\AnimeClient\API\Mapping\{AnimeWatchingStatus, MangaReadingStatus};
use Aviat\AnimeClient\Enum\{APISource, ListType, SyncAction};
use Aviat\AnimeClient\Types\FormItem;
use Aviat\Ion\Di\Exception\ContainerException;
use Aviat\Ion\Di\Exception\NotFoundException;
use Aviat\Ion\Json;
use DateTime;
use Throwable;
use function in_array;

/**
 * Syncs list data between Anilist and Kitsu
 */
final class SyncLists extends BaseCommand {

	/**
	 * Model for making requests to Anilist API
	 * @var Anilist\Model
	 */
	private Anilist\Model $anilistModel;

	/**
	 * Model for making requests to Kitsu API
	 * @var Kitsu\Model
	 */
	private Kitsu\Model $kitsuModel;

	/**
	 * Does the Kitsu API have valid authentication?
	 * @var bool
	 */
	private bool $isKitsuAuthenticated = FALSE;

	/**
	 * Sync Kitsu <=> Anilist
	 *
	 * @param array $args
	 * @param array $options
	 * @throws ContainerException
	 * @throws NotFoundException
	 * @throws Throwable
	 */
	public function execute(array $args, array $options = []): void
	{
		$this->init();

		foreach ([ListType::ANIME, ListType::MANGA] as $type)
		{
			// Main Sync flow
			$this->fetchCount($type);
			$rawData = $this->fetch($type);
			$normalized = $this->transform($type, $rawData);
			$compared = $this->compare($type, $normalized);
			$this->update($type, $compared);
		}
	}

	// ------------------------------------------------------------------------
	// Main sync flow methods
	// ------------------------------------------------------------------------

	/**
	 * Set up dependencies
	 *
	 * @throws ContainerException
	 * @throws NotFoundException
	 */
	protected function init(): void
	{
		$this->setContainer($this->setupContainer());
		$this->setCache($this->container->get('cache'));

		$config = $this->container->get('config');
		$anilistEnabled = $config->get(['anilist', 'enabled']);

		// We can't sync kitsu against itself!
		if ( ! $anilistEnabled)
		{
			$this->echoErrorBox('Anlist API is not enabled. Can not sync.');
			die();
		}

		// Authentication is required to update Kitsu
		$this->isKitsuAuthenticated = $this->container->get('auth')->isAuthenticated();
		if ( ! $this->isKitsuAuthenticated)
		{
			$this->echoWarningBox('Kitsu is not authenticated. Kitsu list can not be updated.');
		}

		$this->anilistModel = $this->container->get('anilist-model');
		$this->kitsuModel = $this->container->get('kitsu-model');
	}

	/**
	 * Get and display the count of items for each API
	 *
	 * @param string $type
	 */
	protected function fetchCount(string $type): void
	{
		$this->echo('Fetching List Counts');
		$progress = new Widgets\ProgressBar($this->getConsole(), 2, 50, FALSE);

		$displayLines = [];

		$kitsuCount = $this->fetchKitsuCount($type);
		$displayLines[] = "Number of Kitsu {$type} list items: {$kitsuCount}";
		$progress->incr();

		$anilistCount = $this->fetchAnilistCount($type);
		$displayLines[] = "Number of Anilist {$type} list items: {$anilistCount}";
		$progress->incr();

		$this->clearLine();

		$this->echoBox($displayLines);
	}

	/**
	 * Get the list data
	 *
	 * @param string $type
	 * @return array
	 */
	protected function fetch(string $type): array
	{
		$this->echo('Fetching List Data');
		$progress = new Widgets\ProgressBar($this->getConsole(), 2, 50, FALSE);

		$anilist = $this->fetchAnilist($type);
		$progress->incr();

		$kitsu = $this->fetchKitsu($type);
		$progress->incr();

		$this->clearLine();

		return [
			'anilist' => $anilist,
			'kitsu' => $kitsu,
		];
	}

	/**
	 * Normalize the list data for comparison
	 *
	 * @param string $type
	 * @param array $data
	 * @return array
	 */
	protected function transform(string $type, array $data): array
	{
		$this->echo('Normalizing List Data');
		$progress = new Widgets\ProgressBar($this->getConsole(), 2, 50, FALSE);

		$kitsu = $this->transformKitsu($type, $data['kitsu']);
		$progress->incr();

		$anilist = $this->transformAnilist($type, $data['anilist']);
		$progress->incr();

		$this->clearLine();

		return [
			'anilist' => $anilist,
			'kitsu' => $kitsu,
		];
	}

	/**
	 * Compare the lists data
	 *
	 * @param string $type
	 * @param array $data
	 * @return array|array[]
	 */
	protected function compare(string $type, array $data): array
	{
		$this->echo('Comparing List Items');

		return $this->compareLists($type, $data['anilist'], $data['kitsu']);
	}

	/**
	 * Updated outdated list items
	 *
	 * @param string $type
	 * @param array $data
	 * @throws Throwable
	 */
	protected function update(string $type, array $data)
	{
		if ( ! empty($data['addToAnilist']))
		{
			$count = count($data['addToAnilist']);
			$this->echoBox("Adding {$count} missing {$type} list items to Anilist");
			$this->updateAnilistListItems($data['addToAnilist'], SyncAction::CREATE, $type);
		}

		if ( ! empty($data['updateAnilist']))
		{
			$count = count($data['updateAnilist']);
			$this->echoBox("Updating {$count} outdated Anilist {$type} list items");
			$this->updateAnilistListItems($data['updateAnilist'], SyncAction::UPDATE, $type);
		}

		if ($this->isKitsuAuthenticated)
		{
			if ( ! empty($data['addToKitsu']))
			{
				$count = count($data['addToKitsu']);
				$this->echoBox("Adding {$count} missing {$type} list items to Kitsu");
				$this->updateKitsuListItems($data['addToKitsu'], SyncAction::CREATE, $type);
			}

			if ( ! empty($data['updateKitsu']))
			{
				$count = count($data['updateKitsu']);
				$this->echoBox("Updating {$count} outdated Kitsu {$type} list items");
				$this->updateKitsuListItems($data['updateKitsu'], SyncAction::UPDATE, $type);
			}
		}
		else
		{
			$this->echoErrorBox('Kitsu is not authenticated, so lists can not be updated');
		}
	}

	// ------------------------------------------------------------------------
	// Fetch helpers
	// ------------------------------------------------------------------------
	private function fetchAnilistCount(string $type)
	{
		$list = $this->fetchAnilist($type);

		if ( ! isset($list['data']['MediaListCollection']['lists']))
		{
			return 0;
		}

		$count = 0;

		foreach ($list['data']['MediaListCollection']['lists'] as $subList)
		{
			$count += array_reduce($subList, fn ($carry, $item) => $carry + count(array_values($item)), 0);
		}

		return $count;
	}

	private function fetchAnilist(string $type): array
	{
		static $list = [
			ListType::ANIME => NULL,
			ListType::MANGA => NULL,
		];

		// This uses a static so I don't have to fetch this list twice for a count
		if ($list[$type] === NULL)
		{
			$list[$type] = $this->anilistModel->getSyncList(strtoupper($type));
		}

		return $list[$type];
	}

	private function fetchKitsuCount(string $type): int
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

		return $kitsuCount;
	}

	private function fetchKitsu(string $type): array
	{
		return $this->kitsuModel->getSyncList($type);
	}

	// ------------------------------------------------------------------------
	// Transform Helpers
	// ------------------------------------------------------------------------

	private function transformKitsu(string $type, array $data): array
	{
		if (empty($data))
		{
			return [];
		}

		if ( ! array_key_exists('included', $data))
		{
			dump($data);
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
				if (is_array($mappingId))
				{
					continue;
				}

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

	private function transformAnilist(string $type, array $data): array
	{
		$uType = ucfirst($type);
		$className = "\\Aviat\\AnimeClient\\API\\Anilist\\Transformer\\{$uType}ListTransformer";
		$transformer = new $className;

		$firstTransformed = [];

		foreach ($data['data']['MediaListCollection']['lists'] as $list)
		{
			$firstTransformed[] = $transformer->untransformCollection($list['entries']);
		}

		$transformed = array_merge_recursive(...$firstTransformed);

		// Key the array by mal_id
		$output = [];
		foreach ($transformed as $item)
		{
			$output[$item['mal_id']] = $item->toArray();
		}

		return $output;
	}

	// ------------------------------------------------------------------------
	// Compare Helpers
	// ------------------------------------------------------------------------

	private function compareLists(string $type, array $anilistList, array $kitsuList): array
	{
		$itemsToAddToAnilist = [];
		$itemsToAddToKitsu = [];
		$anilistUpdateItems = [];
		$kitsuUpdateItems = [];

		$malIds = array_keys($anilistList);
		$kitsuMalIds = array_map('intval', array_column($kitsuList, 'malId'));
		$missingMalIds = array_filter(array_diff($kitsuMalIds, $malIds), fn ($id) => ! in_array($id, $kitsuMalIds));

		// Add items on Anilist, but not Kitsu to Kitsu
		foreach($missingMalIds as $mid)
		{
			if ( ! array_key_exists($mid, $anilistList))
			{
				continue;
			}

			$data = $anilistList[$mid]['data'];
			$data['id'] = $this->kitsuModel->getKitsuIdFromMALId((string)$mid, $type);
			$data['type'] = $type;

			$itemsToAddToKitsu[] = $data;
		}

		foreach($kitsuList as $kitsuItem)
		{
			$malId = $kitsuItem['malId'];

			if (array_key_exists($malId, $anilistList))
			{
				$anilistItem = $anilistList[$malId];

				$item = $this->compareListItems($kitsuItem, $anilistItem);

				if ($item === NULL)
				{
					continue;
				}

				if (in_array('kitsu', $item['updateType'], TRUE))
				{
					$kitsuUpdateItems[] = $item['data'];
				}

				if (in_array('anilist', $item['updateType'], TRUE))
				{
					$anilistUpdateItems[] = $item['data'];
				}

				continue;
			}

			$statusMap = ($type === ListType::ANIME) ? AnimeWatchingStatus::class : MangaReadingStatus::class;

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
	private function compareListItems(array $kitsuItem, array $anilistItem): ?array
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
		$sameRating = $diff['rating'] === 0;
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
			if (
				$dateDiff === 1 &&
				$kitsuItem['data']['rating'] !== 0 &&
				$kitsuItem['data']['ratingTwenty'] !== 0
			)
			{
				$update['data']['ratingTwenty'] = $kitsuItem['data']['ratingTwenty'];
				$return['updateType'][] = 'anilist';
			}
			else if($dateDiff === -1 && $anilistItem['data']['rating'] !== 0)
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

		// No changes? Let's bail!
		if (empty($return['updateType']))
		{
			return $return;
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

		return $return;
	}

	// ------------------------------------------------------------------------
	// Update Helpers
	// ------------------------------------------------------------------------

	/**
	 * Create/Update list items on Kitsu
	 *
	 * @param array $itemsToUpdate
	 * @param string $action
	 * @param string $type
	 * @throws Throwable
	 */
	private function updateKitsuListItems(array $itemsToUpdate, string $action = SyncAction::UPDATE, string $type = ListType::ANIME): void
	{
		$requester = new ParallelAPIRequest();
		foreach($itemsToUpdate as $item)
		{
			if ($action === SyncAction::UPDATE)
			{
				$requester->addRequest(
					$this->kitsuModel->updateListItem(FormItem::from($item))
				);
			}
			else if ($action === SyncAction::CREATE)
			{
				$maybeRequest = $this->kitsuModel->createListItem($item);
				if ($maybeRequest === NULL)
				{
					$this->echoWarning("Skipped creating Kitsu {$type} due to missing id ¯\_(ツ)_/¯");
					continue;
				}
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
				$verb = ($action === SyncAction::UPDATE) ? 'updated' : 'created';
				$this->echoSuccess("Successfully {$verb} Kitsu {$type} list item with id: {$id}");
				continue;
			}

			// Show a different message when you have an episode count mismatch
			if (isset($responseData['errors'][0]['title']))
			{
				$errorTitle = $responseData['errors'][0]['title'];

				if ($errorTitle === 'cannot exceed length of media')
				{
					$this->echoWarning("Skipped Kitsu {$type} {$id} due to episode count mismatch with other API");
					continue;
				}
			}

			dump($responseData);
			$verb = ($action === SyncAction::UPDATE) ? SyncAction::UPDATE : SyncAction::CREATE;
			$this->echoError("Failed to {$verb} Kitsu {$type} list item with id: {$id}");

		}
	}

	/**
	 * Create/Update list items on Anilist
	 *
	 * @param array $itemsToUpdate
	 * @param string $action
	 * @param string $type
	 * @throws Throwable
	 */
	private function updateAnilistListItems(array $itemsToUpdate, string $action = SyncAction::UPDATE, string $type = ListType::ANIME): void
	{
		$requester = new ParallelAPIRequest();

		foreach($itemsToUpdate as $item)
		{
			if ($action === SyncAction::UPDATE)
			{
				$requester->addRequest(
					$this->anilistModel->updateListItem(FormItem::from($item), $type)
				);
			}
			else if ($action === SyncAction::CREATE)
			{
				try
				{
					$requester->addRequest($this->anilistModel->createFullListItem($item, $type));
				}
				catch (MissingIdException $e)
				{
					// Case where there's a MAL mapping from Kitsu, but no equivalent Anlist item
					$id = $item['mal_id'];
					$this->echoWarning("Skipping Anilist ${type} with MAL id: {$id} due to missing mapping");
				}
			}
		}

		$responses = $requester->makeRequests();

		foreach($responses as $key => $response)
		{
			$id = $itemsToUpdate[$key]['mal_id'];

			$responseData = Json::decode($response);

			if ( ! array_key_exists('errors', $responseData))
			{
				$verb = ($action === SyncAction::UPDATE) ? 'updated' : 'created';
				$this->echoSuccess("Successfully {$verb} Anilist {$type} list item with id: {$id}");
			}
			else
			{
				dump($responseData);
				$verb = ($action === SyncAction::UPDATE) ? SyncAction::UPDATE : SyncAction::CREATE;
				$this->echoError("Failed to {$verb} Anilist {$type} list item with id: {$id}");
			}
		}
	}

	// ------------------------------------------------------------------------
	// Other Helpers
	// ------------------------------------------------------------------------

	/**
	 * Filter Kitsu mappings for the specified type
	 *
	 * @param array $includes
	 * @param string $type
	 * @return array
	 */
	private function filterMappings(array $includes, string $type = ListType::ANIME): array
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
}
