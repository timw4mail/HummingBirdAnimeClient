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

namespace Aviat\AnimeClient\Command;


use Aviat\AnimeClient\API\{
	APIRequestBuilder,
	JsonAPI,
	FailedResponseException,
	ParallelAPIRequest
};

use Aviat\Ion\Json;


final class MALIDCheck extends BaseCommand {

	private $kitsuModel;

	/**
	 * Check MAL mapping validity
	 *
	 * @param array $args
	 * @param array $options
	 * @throws \Aviat\Ion\Di\Exception\ContainerException
	 * @throws \Aviat\Ion\Di\Exception\NotFoundException
	 */
	public function execute(array $args, array $options = []): void
	{
		$this->setContainer($this->setupContainer());
		$this->setCache($this->container->get('cache'));
		$this->kitsuModel = $this->container->get('kitsu-model');

		// @TODO: Stuff!
	}

	private function getListIds()
	{
		$this->getListCounts('anime');
		$this->getListCounts('manga');

	}

	private function getListCounts($type): void
	{
		$uType = ucfirst($type);

		$kitsuCount = 0;
		try
		{
			$kitsuCount = $this->kitsuModel->{"get{$uType}ListCount"}();
		} catch (FailedResponseException $e)
		{
			dump($e);
		}

		$this->echoBox("Number of Kitsu {$type} list items: {$kitsuCount}");
	}

	/**
	 * Format a kitsu list for the sake of comparision
	 *
	 * @param string $type
	 * @return array
	 */
	protected function formatKitsuList(string $type = 'anime'): array
	{
		$data = $this->kitsuModel->{'getFull' . ucfirst($type) . 'List'}();

		if (empty($data))
		{
			return [];
		}

		$includes = JsonAPI::organizeIncludes($data['included']);
		$includes['mappings'] = $this->filterMappings($includes['mappings'], $type);

		$output = [];

		foreach ($data['data'] as $listItem)
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
	 * Filter Kitsu mappings for the specified type
	 *
	 * @param array $includes
	 * @param string $type
	 * @return array
	 */
	protected function filterMappings(array $includes, string $type = 'anime'): array
	{
		$output = [];

		foreach ($includes as $id => $mapping)
		{
			if ($mapping['externalSite'] === "myanimelist/{$type}")
			{
				$output[$id] = $mapping;
			}
		}

		return $output;
	}

	protected function checkMALIds(array $kitsuList, string $type)
	{
		$requester = new ParallelAPIRequest();
	}

	/**
	 * Create/Update list items on Kitsu
	 *
	 * @param array $itemsToUpdate
	 * @param string $action
	 * @param string $type
	 */
	protected function updateKitsuListItems(array $itemsToUpdate, string $action = 'update', string $type = 'anime'): void
	{
		$requester = new ParallelAPIRequest();
		foreach ($itemsToUpdate as $item)
		{
			if ($action === 'update')
			{
				$requester->addRequest($this->kitsuModel->updateListItem($item));
			} else if ($action === 'create')
			{
				$requester->addRequest($this->kitsuModel->createListItem($item));
			}
		}

		$responses = $requester->makeRequests();

		foreach ($responses as $key => $response)
		{
			$responseData = Json::decode($response);

			$id = $itemsToUpdate[$key]['id'];
			if ( ! array_key_exists('errors', $responseData))
			{
				$verb = ($action === 'update') ? 'updated' : 'created';
				$this->echoBox("Successfully {$verb} Kitsu {$type} list item with id: {$id}");
			} else
			{
				dump($responseData);
				$verb = ($action === 'update') ? 'update' : 'create';
				$this->echoBox("Failed to {$verb} Kitsu {$type} list item with id: {$id}");
			}
		}
	}
}