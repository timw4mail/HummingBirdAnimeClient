<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshome.page>
 * @copyright   2015 - 2022  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Command;

use Aviat\AnimeClient\API\Anilist;

use Aviat\AnimeClient\API\Mapping\{AnimeWatchingStatus, MangaReadingStatus};
use Aviat\AnimeClient\API\{
	Anilist\MissingIdException,
	ParallelAPIRequest
};
use Aviat\AnimeClient\Enum\{MediaType, SyncAction};
use Aviat\AnimeClient\Types\FormItem;
use Aviat\AnimeClient\{API, Enum};
use Aviat\Ion\Di\Exception\{ContainerException, NotFoundException};
use Aviat\Ion\{Json, JsonException};
use ConsoleKit\Widgets;
use DateTime;
use Throwable;

/**
 * Syncs list data between Anilist and Kitsu
 */
final class SyncLists extends BaseCommand
{
	protected const KITSU_GREATER = 1;
	protected const ANILIST_GREATER = -1;
	protected const SAME = 0;

	/**
	 * Model for making requests to Anilist API
	 */
	private Anilist\Model $anilistModel;

	/**
	 * Model for making requests to Kitsu API
	 */
	private API\Kitsu\Model $kitsuModel;

	/**
	 * Does the Kitsu API have valid authentication?
	 */
	private bool $isKitsuAuthenticated = FALSE;

	/**
	 * Sync Kitsu <=> Anilist
	 *
	 * @throws ContainerException
	 * @throws NotFoundException
	 * @throws Throwable
	 */
	public function execute(array $args, array $options = []): void
	{
		$this->init();

		foreach ([MediaType::MANGA, MediaType::ANIME] as $type)
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
		$anilistEnabled = $config->get([Enum\API::ANILIST, 'enabled']);

		// We can't sync kitsu against itself!
		if ( ! $anilistEnabled)
		{
			$this->echoErrorBox('Anlist API is not enabled. Can not sync.');

			exit();
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
	 * @return array<string, mixed[]>
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
			Enum\API::ANILIST => $anilist,
			Enum\API::KITSU => $kitsu,
		];
	}

	/**
	 * Normalize the list data for comparison
	 *
	 * @return array<string, mixed[]>
	 */
	protected function transform(string $type, array $data): array
	{
		$this->echo('Normalizing List Data');
		$progress = new Widgets\ProgressBar($this->getConsole(), 2, 50, FALSE);

		$kitsu = $this->transformKitsu($type, $data[Enum\API::KITSU]);
		$progress->incr();

		$anilist = $this->transformAnilist($type, $data[Enum\API::ANILIST]);
		$progress->incr();

		$this->clearLine();

		return [
			Enum\API::ANILIST => $anilist,
			Enum\API::KITSU => $kitsu,
		];
	}

	/**
	 * Compare the lists data
	 *
	 * @return array<string, mixed[]>
	 */
	protected function compare(string $type, array $data): array
	{
		$this->echo('Comparing List Items');

		return $this->compareLists($type, $data[Enum\API::ANILIST], $data[Enum\API::KITSU]);
	}

	/**
	 * Updated outdated list items
	 *
	 * @throws Throwable
	 */
	protected function update(string $type, array $data): void
	{
		if ( ! empty($data['addToAnilist']))
		{
			$count = is_countable($data['addToAnilist']) ? count($data['addToAnilist']) : 0;
			$this->echoBox("Adding {$count} missing {$type} list items to Anilist");
			$this->updateAnilistListItems($data['addToAnilist'], SyncAction::CREATE, $type);
		}

		if ( ! empty($data['updateAnilist']))
		{
			$count = is_countable($data['updateAnilist']) ? count($data['updateAnilist']) : 0;
			$this->echoBox("Updating {$count} outdated Anilist {$type} list items");
			$this->updateAnilistListItems($data['updateAnilist'], SyncAction::UPDATE, $type);
		}

		if ($this->isKitsuAuthenticated)
		{
			if ( ! empty($data['addToKitsu']))
			{
				$count = is_countable($data['addToKitsu']) ? count($data['addToKitsu']) : 0;
				$this->echoBox("Adding {$count} missing {$type} list items to Kitsu");
				$this->updateKitsuListItems($data['addToKitsu'], SyncAction::CREATE, $type);
			}

			if ( ! empty($data['updateKitsu']))
			{
				$count = is_countable($data['updateKitsu']) ? count($data['updateKitsu']) : 0;
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
	private function fetchAnilistCount(string $type): int
	{
		$list = $this->fetchAnilist($type);

		if ( ! isset($list['data']['MediaListCollection']['lists']))
		{
			return 0;
		}

		$count = 0;

		foreach ($list['data']['MediaListCollection']['lists'] as $subList)
		{
			$count += array_reduce($subList, static fn ($carry, $item) => $carry + count(array_values($item)), 0);
		}

		return $count;
	}

	/**
	 * @return mixed[]
	 */
	private function fetchAnilist(string $type): array
	{
		static $list = [
			MediaType::ANIME => NULL,
			MediaType::MANGA => NULL,
		];

		// This uses a static so I don't have to fetch this list twice for a count
		if ($list[$type] === NULL)
		{
			try
			{
				$list[$type] = $this->anilistModel->getSyncList(strtoupper($type));
			}
			catch (JsonException)
			{
				$this->echoErrorBox('Anlist API exception. Can not sync.');

				exit();
			}
		}

		return $list[$type];
	}

	private function fetchKitsuCount(string $type): int
	{
		$uType = ucfirst($type);

		return $this->kitsuModel->{"get{$uType}ListCount"}() ?? 0;
	}

	/**
	 * @return mixed[]
	 */
	private function fetchKitsu(string $type): array
	{
		return $this->kitsuModel->getSyncList($type);
	}

	// ------------------------------------------------------------------------
	// Transform Helpers
	// ------------------------------------------------------------------------
	/**
	 * @return mixed[]
	 */
	private function transformKitsu(string $type, array $data): array
	{
		if (empty($data))
		{
			return [];
		}

		$output = [];

		foreach ($data as $listItem)
		{
			// If there's no mapping, we can't sync, so continue
			if ( ! is_array($listItem['media']['mappings']['nodes']))
			{
				continue;
			}

			$malId = NULL;

			foreach ($listItem['media']['mappings']['nodes'] as $mapping)
			{
				$uType = strtoupper($type);
				if ($mapping['externalSite'] === "MYANIMELIST_{$uType}")
				{
					$malId = $mapping['externalId'];
					break;
				}
			}

			// Skip to the next item if there isn't a Anilist ID
			if ($malId === NULL)
			{
				continue;
			}

			$output[$listItem['media']['id']] = [
				'id' => $listItem['media']['id'],
				'slug' => $listItem['media']['slug'],
				'malId' => $malId,
				'data' => [
					'notes' => $listItem['notes'],
					'private' => $listItem['private'],
					'progress' => $listItem['progress'],
					// Comparision is done on 1-10 scale,
					// Kitsu returns 1-20 scale.
					'rating' => (int) $listItem['rating'] / 2,
					'reconsumeCount' => $listItem['reconsumeCount'],
					'reconsuming' => $listItem['reconsuming'],
					'status' => strtolower($listItem['status']),
					'updatedAt' => $listItem['progressedAt'],
				],
			];
		}

		return $output;
	}

	/**
	 * @return array<int|string, mixed>
	 */
	private function transformAnilist(string $type, array $data): array
	{
		$uType = ucfirst($type);
		$className = "\\Aviat\\AnimeClient\\API\\Anilist\\Transformer\\{$uType}ListTransformer";
		$transformer = new $className();

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
	/**
	 * @return array<string, mixed[]>
	 */
	private function compareLists(string $type, array $anilistList, array $kitsuList): array
	{
		$itemsToAddToAnilist = [];
		$itemsToAddToKitsu = [];
		$anilistUpdateItems = [];
		$kitsuUpdateItems = [];

		$malIds = array_keys($anilistList);
		$kitsuMalIds = array_map('intval', array_column($kitsuList, 'malId'));
		$missingMalIds = array_filter($malIds, static fn ($id) => ! in_array($id, $kitsuMalIds, TRUE));

		// Add items on Anilist, but not Kitsu to Kitsu
		foreach ($missingMalIds as $mid)
		{
			if ( ! array_key_exists($mid, $anilistList))
			{
				continue;
			}

			$data = $anilistList[$mid]['data'];
			$data['id'] = $this->kitsuModel->getKitsuIdFromMALId((string) $mid, $type);
			$data['type'] = $type;

			$itemsToAddToKitsu[] = $data;
		}

		foreach ($kitsuList as $kitsuItem)
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

				if (in_array(Enum\API::KITSU, $item['updateType'], TRUE))
				{
					$kitsuUpdateItems[] = $item['data'];
				}

				if (in_array(Enum\API::ANILIST, $item['updateType'], TRUE))
				{
					$anilistUpdateItems[] = $item['data'];
				}

				continue;
			}

			$statusMap = ($type === MediaType::ANIME) ? AnimeWatchingStatus::class : MangaReadingStatus::class;

			// Looks like this item only exists on Kitsu
			$kItem = $kitsuItem['data'];
			$newItemStatus = ($kItem['reconsuming'] === TRUE) ? 'REPEATING' : $statusMap::KITSU_TO_ANILIST[$kItem['status']];
			$itemsToAddToAnilist[] = [
				'mal_id' => $malId,
				'data' => [
					'notes' => $kItem['notes'],
					'private' => $kItem['private'],
					'progress' => $kItem['progress'],
					'repeat' => $kItem['reconsumeCount'],
					'score' => $kItem['rating'] * 10, // 100 point score on Anilist
					'status' => $newItemStatus,
				],
			];
		}

		return [
			'addToAnilist' => $itemsToAddToAnilist,
			'updateAnilist' => $anilistUpdateItems,
			'addToKitsu' => $itemsToAddToKitsu,
			'updateKitsu' => $kitsuUpdateItems,
		];
	}

	/**
	 * Compare two list items, and return the out of date one, if one exists
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
		$dateDiff = ($kitsuItem['data']['updatedAt'] !== NULL)
			? new DateTime($kitsuItem['data']['updatedAt']) <=> new DateTime((string) $anilistItem['data']['updatedAt'])
			: 0;

		foreach ($compareKeys as $key)
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
			'data' => [],
		];
		$return = [
			'updateType' => [],
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
			$return['updateType'][] = Enum\API::KITSU;
		}

		// If status is the same, and progress count is different, use greater progress
		if ($sameStatus && ( ! $sameProgress))
		{
			if ($diff['progress'] === self::KITSU_GREATER)
			{
				$update['data']['progress'] = $kitsuItem['data']['progress'];
				$return['updateType'][] = Enum\API::ANILIST;
			}
			elseif ($diff['progress'] === self::ANILIST_GREATER)
			{
				$update['data']['progress'] = $anilistItem['data']['progress'];
				$return['updateType'][] = Enum\API::KITSU;
			}
		}

		// If status is different, use the status of the more recently updated item
		if ( ! $sameStatus)
		{
			if ($dateDiff === self::KITSU_GREATER)
			{
				$update['data']['status'] = $kitsuItem['data']['status'];
				$return['updateType'][] = Enum\API::ANILIST;
			}
			elseif ($dateDiff === self::ANILIST_GREATER)
			{
				$update['data']['status'] = $anilistItem['data']['status'];
				$return['updateType'][] = Enum\API::KITSU;
			}
		}

		// If status and progress are different, it's a bit more complicated...
		// But, at least for now, assume newer record is correct
		if ( ! ($sameStatus || $sameProgress))
		{
			if ($dateDiff === self::KITSU_GREATER)
			{
				$update['data']['status'] = $kitsuItem['data']['status'];

				if ((int) $kitsuItem['data']['progress'] !== 0)
				{
					$update['data']['progress'] = $kitsuItem['data']['progress'];
				}

				$return['updateType'][] = Enum\API::ANILIST;
			}
			elseif ($dateDiff === self::ANILIST_GREATER)
			{
				$update['data']['status'] = $anilistItem['data']['status'];

				if ((int) $anilistItem['data']['progress'] !== 0)
				{
					$update['data']['progress'] = $kitsuItem['data']['progress'];
				}

				$return['updateType'][] = Enum\API::KITSU;
			}
		}

		// Use the first set rating, otherwise use the newer rating
		if ( ! $sameRating)
		{
			if (
				$dateDiff === self::KITSU_GREATER
				&& $kitsuItem['data']['rating'] !== 0
				&& $kitsuItem['data']['ratingTwenty'] !== 0
			) {
				$update['data']['ratingTwenty'] = $kitsuItem['data']['rating'];
				$return['updateType'][] = Enum\API::ANILIST;
			}
			elseif ($dateDiff === self::ANILIST_GREATER && $anilistItem['data']['rating'] !== 0)
			{
				$update['data']['ratingTwenty'] = $anilistItem['data']['rating'] * 2;
				$return['updateType'][] = Enum\API::KITSU;
			}
		}

		// If notes are set, use kitsu, otherwise, set kitsu from anilist
		if ( ! $sameNotes)
		{
			if ( ! empty($kitsuItem['data']['notes']))
			{
				$update['data']['notes'] = $kitsuItem['data']['notes'];
				$return['updateType'][] = Enum\API::ANILIST;
			}
			else
			{
				$update['data']['notes'] = $anilistItem['data']['notes'];
				$return['updateType'][] = Enum\API::KITSU;
			}
		}

		// Assume the larger reconsumeCount is correct
		if ( ! $sameRewatchCount)
		{
			if ($diff['reconsumeCount'] === self::KITSU_GREATER)
			{
				$update['data']['reconsumeCount'] = $kitsuItem['data']['reconsumeCount'];
				$return['updateType'][] = Enum\API::ANILIST;
			}
			elseif ($diff['reconsumeCount'] === self::ANILIST_GREATER)
			{
				$update['data']['reconsumeCount'] = $anilistItem['data']['reconsumeCount'];
				$return['updateType'][] = Enum\API::KITSU;
			}
		}

		// No changes? Let's bail!
		if (empty($return['updateType']))
		{
			return $return;
		}

		$return['meta'] = [
			Enum\API::KITSU => $kitsuItem['data'],
			Enum\API::ANILIST => $anilistItem['data'],
			'dateDiff' => $dateDiff,
			'diff' => $diff,
		];
		$return['data'] = $update;
		$return['updateType'] = array_unique($return['updateType']);

		// Fill in missing data values for update
		// so I don't have to create a really complex graphql query
		// to handle each combination of fields
		if ($return['updateType'][0] === Enum\API::ANILIST)
		{
			// Anilist GraphQL expects a rating from 1-100
			$prevData = [
				'notes' => $kitsuItem['data']['notes'],
				'private' => $kitsuItem['data']['private'],
				'progress' => $kitsuItem['data']['progress'],
				// Transformed Kitsu data returns a rating from 1-10
				// Anilist expects a rating from 1-100
				'rating' => $kitsuItem['data']['rating'] * 10,
				'reconsumeCount' => $kitsuItem['data']['reconsumeCount'],
				'reconsuming' => $kitsuItem['data']['reconsuming'],
				'status' => $kitsuItem['data']['status'],
			];

			$return['data']['data'] = array_merge($prevData, $return['data']['data']);
		}
		elseif ($return['updateType'][0] === Enum\API::KITSU)
		{
			$prevData = [
				'notes' => $anilistItem['data']['notes'],
				'private' => $anilistItem['data']['private'],
				'progress' => $anilistItem['data']['progress'] ?? 0,
				// Anilist returns a rating between 1-100
				// Kitsu expects a rating from 1-20
				'rating' => (((int) $anilistItem['data']['rating']) > 0)
					? (int) $anilistItem['data']['rating'] / 5
					: 0,
				'reconsumeCount' => $anilistItem['data']['reconsumeCount'],
				'reconsuming' => $anilistItem['data']['reconsuming'],
				'status' => $anilistItem['data']['status'],
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
	 * @throws Throwable
	 */
	private function updateKitsuListItems(array $itemsToUpdate, string $action = SyncAction::UPDATE, string $type = MediaType::ANIME): void
	{
		$requester = new ParallelAPIRequest();

		foreach ($itemsToUpdate as $item)
		{
			if ($action === SyncAction::UPDATE)
			{
				$requester->addRequest(
					$this->kitsuModel->updateListItem(FormItem::from($item))
				);
			}
			elseif ($action === SyncAction::CREATE)
			{
				$maybeRequest = $this->kitsuModel->createListItem($item);
				if ($maybeRequest === NULL)
				{
					$this->echoWarning("Skipped creating Kitsu {$type} due to missing id ¯\\_(ツ)_/¯");

					continue;
				}

				$requester->addRequest($maybeRequest);
			}
		}

		$responses = $requester->makeRequests();

		foreach ($responses as $key => $response)
		{
			$responseData = Json::decode($response);

			$id = $itemsToUpdate[$key]['id'];
			$mal_id = $itemsToUpdate[$key]['mal_id'] ?? NULL;
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

			dump([
				'problem' => 'Failed to update kitsu list item',
				'syncDate' => $itemsToUpdate[$key],
				'responseData' => $responseData,
			]);
			$verb = ($action === SyncAction::UPDATE) ? SyncAction::UPDATE : SyncAction::CREATE;
			$this->echoError("Failed to {$verb} Kitsu {$type} list item with id: {$id}, and mal_id: {$mal_id}");
		}
	}

	/**
	 * Create/Update list items on Anilist
	 *
	 * @throws Throwable
	 */
	private function updateAnilistListItems(array $itemsToUpdate, string $action = SyncAction::UPDATE, string $type = MediaType::ANIME): void
	{
		$requester = new ParallelAPIRequest();

		foreach ($itemsToUpdate as $item)
		{
			if ($action === SyncAction::UPDATE)
			{
				$maybeRequest = $this->anilistModel->updateListItem(FormItem::from($item), $type);
				if ($maybeRequest !== NULL)
				{
					$requester->addRequest($maybeRequest);
				}
			}
			else
			{
				if ($action === SyncAction::CREATE)
				{
					try
					{
						$requester->addRequest($this->anilistModel->createFullListItem($item, $type));
					}
					catch (MissingIdException)
					{
						// Case where there's a MAL mapping from Kitsu, but no equivalent Anlist item
						$id = $item['mal_id'];
						$this->echoWarning("Skipping Anilist {$type} with MAL id: {$id} due to missing mapping");
					}
				}
			}
		}

		$responses = $requester->makeRequests();

		foreach ($responses as $key => $response)
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
				dump([
					'problem' => 'Failed to update anilist list item',
					'syncDate' => $itemsToUpdate[$key],
					'responseData' => $responseData,
				]);
				$verb = ($action === SyncAction::UPDATE) ? SyncAction::UPDATE : SyncAction::CREATE;
				$this->echoError("Failed to {$verb} Anilist {$type} list item with id: {$id}");
			}
		}
	}
}
