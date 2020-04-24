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

namespace Aviat\AnimeClient\API\Kitsu\Transformer;

use Aviat\AnimeClient\Types\HistoryItem;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

abstract class HistoryTransformer {
	/**
	 * @var string The media type
	 */
	protected string $type;

	/**
	 * @var string The message for watching/reading a single episode/chapter
	 */
	protected string $progressAction;

	/**
	 * @var string The message for rewatching/rereading episode(s)/chapter(s)
	 */
	protected string $reconsumeAction;

	/**
	 * @var string The message for going through a large number of media in a series
	 */
	protected string $largeAggregateAction;

	/**
	 * @var string The status for items you are rewatching/rereading
	 */
	protected string $reconsumingStatus;

	/**
	 * @var array The mapping of api status to display status
	 */
	protected array $statusMap;

	/**
	 * Convert raw history
	 *
	 * @param array $data
	 * @return array
	 */
	public function transform(array $data): array
	{
		$output = [];

		foreach ($data as $entry)
		{
			if ( ! isset($entry['relationships'][$this->type]))
			{
				continue;
			}

			$kind = $entry['attributes']['kind'];

			if ($kind === 'progressed' && ! empty($entry['attributes']['changedData']['progress']))
			{
				$output[] = $this->transformProgress($entry);
			}
			else if ($kind === 'updated')
			{
				$output[] = $this->transformUpdated($entry);
			}
		}

		return $this->aggregate($output);
	}

	/**
	 * Combine consecutive 'progressed' events
	 *
	 * @param array $singles
	 * @return array
	 */
	protected function aggregate (array $singles): array
	{
		$output = [];

		$count = count($singles);
		for ($i = 0; $i < $count; $i++)
		{
			$entries = [];
			$entry = $singles[$i];
			$prevTitle = $entry['title'];
			$nextId = $i;
			$next = $singles[$nextId];
			while (
				$next['kind'] === 'progressed' &&
				$next['title'] === $prevTitle
			) {
				$entries[] = $next;
				$prevTitle = $next['title'];

				if ($nextId + 1 < $count)
				{
					$nextId++;
					$next = $singles[$nextId];
					continue;
				}

				break;
			}

			if (count($entries) > 1)
			{
				$items = [];
				$updated = [];

				foreach ($entries as $e)
				{
					$progressItem = $e['original']['attributes']['changedData']['progress'];
					$items[] = array_pop($progressItem);
					$updated[] = $e['updated'];
				}
				$firstItem = min($items);
				$lastItem = max($items);
				$firstUpdate = min($updated);
				$lastUpdate = max($updated);

				$title = $entries[0]['title'];

				if ($this->isReconsuming($entries[0]['original']))
				{
					$action = "{$this->reconsumeAction}s {$firstItem}-{$lastItem}";
				}
				else
				{
					$action = (count($entries) > 3)
						? "{$this->largeAggregateAction} {$firstItem}-{$lastItem}"
						: "{$this->progressAction}s {$firstItem}-{$lastItem}";
				}

				$output[] = HistoryItem::from([
					'action' => $action,
					'coverImg' => $entries[0]['coverImg'],
					'dateRange' => [$firstUpdate, $lastUpdate],
					'isAggregate' => true,
					'original' => $entries,
					'title' => $title,
					'updated' => $entries[0]['updated'],
					'url' => $entries[0]['url'],
				]);

				// Skip the rest of the aggregate in the main loop
				$i += count($entries) - 1;
				continue;
			}

			$output[] = $entry;
		}

		return $output;
	}

	protected function transformProgress (array $entry): HistoryItem
	{
		$id = array_keys($entry['relationships'][$this->type])[0];
		$data = $entry['relationships'][$this->type][$id]['attributes'];
		$title = $this->linkTitle($data);
		$imgUrl = "images/{$this->type}/{$id}.webp";
		$item = end($entry['attributes']['changedData']['progress']);

		$action = ($this->isReconsuming($entry))
			? "{$this->reconsumeAction} {$item}"
			: "{$this->progressAction} {$item}";

		return HistoryItem::from([
			'action' => $action,
			'coverImg' => $imgUrl,
			'kind' => 'progressed',
			'original' => $entry,
			'title' => $title,
			'updated' => $this->parseDate($entry['attributes']['updatedAt']),
			'url' => $this->getUrl($data),
		]);
	}

	protected function transformUpdated($entry): HistoryItem
	{
		$id = array_keys($entry['relationships'][$this->type])[0];
		$data = $entry['relationships'][$this->type][$id]['attributes'];
		$title = $this->linkTitle($data);
		$imgUrl = "images/{$this->type}/{$id}.webp";

		$kind = array_key_first($entry['attributes']['changedData']);

		if ($kind === 'status')
		{
			$status = array_pop($entry['attributes']['changedData']['status']);
			$statusName = $this->statusMap[$status];

			if ($this->isReconsuming($entry))
			{
				$statusName = ($statusName === 'Completed')
					? "Finished {$this->reconsumingStatus}"
					: $this->reconsumingStatus;
			}

			return HistoryItem::from([
				'action' => $statusName,
				'coverImg' => $imgUrl,
				'kind' => 'updated',
				'original' => $entry,
				'title' => $title,
				'updated' => $this->parseDate($entry['attributes']['updatedAt']),
				'url' => $this->getUrl($data),
			]);
		}

		return $entry;
	}

	protected function linkTitle (array $data): string
	{
		return $data['canonicalTitle'];
	}

	protected function parseDate (string $date): DateTimeImmutable
	{
		$dateTime = DateTimeImmutable::createFromFormat(
			DateTimeInterface::RFC3339_EXTENDED,
			$date
		);

		return $dateTime->setTimezone(new DateTimeZone(date_default_timezone_get()));
	}

	protected function getUrl (array $data): string
	{
		return "/{$this->type}/details/{$data['slug']}";
	}

	protected function isReconsuming ($entry): bool
	{
		$le = $this->getLibraryEntry($entry);
		return $le['reconsuming'];
	}

	private function getLibraryEntry ($entry): ?array
	{
		if ( ! isset($entry['relationships']['libraryEntry']['libraryEntries']))
		{
			return NULL;
		}

		$libraryEntries = $entry['relationships']['libraryEntry']['libraryEntries'];
		$id = array_keys($libraryEntries)[0];

		return $libraryEntries[$id]['attributes'];
	}
}