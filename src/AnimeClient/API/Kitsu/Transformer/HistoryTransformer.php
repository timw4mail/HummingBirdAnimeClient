<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8.4
 *
 * @copyright   2015 - 2026  Timothy J. Warren <tim@timshome.page>
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.3
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu\Transformer;

use Aviat\AnimeClient\Kitsu;
use Aviat\AnimeClient\Types\HistoryItem;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

abstract class HistoryTransformer
{
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
	 * @var array<string, string> The mapping of api status to display status
	 */
	protected array $statusMap = [];

	/**
	 * Convert raw history
	 *
	 * @param array<string, mixed> $data
	 * @return array<HistoryItem>
	 */
	public function transform(array $data): array
	{
		$base = $data['data']['findProfileBySlug']['libraryEvents']['nodes'] ?? [];
		$output = [];

		foreach ($base as $entry)
		{
			// Filter out other media types
			if (strtolower($entry['media']['__typename']) !== $this->type)
			{
				continue;
			}

			// Hide private library entries
			if ($entry['libraryEntry']['private'] === true)
			{
				continue;
			}

			$kind = strtolower($entry['kind']);

			if ($kind === 'progressed' && $entry['changedData']['progress'] !== null)
			{
				$transformed = $this->transformProgress($entry);
				if ($transformed !== null)
				{
					$output[] = $transformed;
				}
			}
			elseif ($kind === 'updated')
			{
				$output[] = $this->transformUpdated($entry);
			}
		}

		return $this->aggregate($output);
	}

	/**
	 * Combine consecutive 'progressed' events
	 *
	 * @param list<HistoryItem> $singles
	 * @return list<HistoryItem>
	 */
	protected function aggregate(array $singles): array
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

			while ($next['kind'] === 'progressed' && $next['title'] === $prevTitle)
			{
				$entries[] = $next;
				$prevTitle = $next['title'];

				if (($nextId + 1) < $count)
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
					$progressItem = $e['original']['changedData']['progress'];
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
					$action = count($entries) > 3
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

	/**
	 * @param array<string, mixed> $entry
	 * @return HistoryItem|null
	 */
	protected function transformProgress(array $entry): null|HistoryItem
	{
		$data = $entry['media'];
		$title = $this->linkTitle($data);
		$item = end($entry['changedData']['progress']);

		// No showing episode 0 nonsense
		if ((int) $item === 0)
		{
			return null;
		}

		// Hide the last episode update (Anime)
		foreach (['episodeCount', 'chapterCount'] as $count)
		{
			if (! array_key_exists($count, $entry['media']))
			{
				continue;
			}

			$update = $entry['changedData']['progress'][1] ?? 0;
			if ($update === $entry['media'][$count])
			{
				return null;
			}
		}

		$action = $this->isReconsuming($entry)
			? "{$this->reconsumeAction} {$item}"
			: "{$this->progressAction} {$item}";

		return HistoryItem::from([
			'action' => $action,
			'coverImg' => Kitsu::getPosterImage($data, 0),
			'kind' => 'progressed',
			'original' => $entry,
			'title' => $title,
			'updated' => $this->parseDate($entry['updatedAt']),
			'url' => $this->getUrl($data),
		]);
	}

	/**
	 * @param array<string, mixed> $entry
	 * @return HistoryItem
	 */
	protected function transformUpdated(array $entry): HistoryItem
	{
		$data = $entry['media'];
		$title = $this->linkTitle($data);

		$kind = array_key_first($entry['changedData']);

		if ($kind === 'status')
		{
			$status = array_pop($entry['changedData']['status']);
			$statusName = $this->statusMap[$status];

			if ($this->isReconsuming($entry))
			{
				$statusName = $statusName === 'Completed'
					? "Finished {$this->reconsumingStatus}"
					: $this->reconsumingStatus;
			}

			return HistoryItem::from([
				'action' => $statusName,
				'coverImg' => Kitsu::getPosterImage($data, 0),
				'kind' => 'updated',
				'original' => $entry,
				'title' => $title,
				'updated' => $this->parseDate($entry['updatedAt']),
				'url' => $this->getUrl($data),
			]);
		}

		return HistoryItem::from([
			'coverImg' => Kitsu::getPosterImage($data, 0),
			'kind' => 'updated',
			'original' => $entry,
			'title' => $title,
			'updated' => $this->parseDate($entry['updatedAt']),
			'url' => $this->getUrl($data),
		]);
	}

	/**
	 * @param array<string, mixed> $data
	 * @return string
	 */
	protected function linkTitle(array $data): string
	{
		return $data['titles']['canonical'];
	}

	protected function parseDate(string $date): DateTimeImmutable
	{
		$dateTime = DateTimeImmutable::createFromFormat(
			DateTimeInterface::RFC3339,
			$date,
		);

		if ($dateTime === false)
		{
			return new DateTimeImmutable();
		}

		return $dateTime->setTimezone(new DateTimeZone(date_default_timezone_get()));
	}

	/**
	 * @param array<string, mixed> $data
	 * @return string
	 */
	protected function getUrl(array $data): string
	{
		return "/{$this->type}/details/{$data['slug']}";
	}

	/**
	 * @param array<string, mixed> $entry
	 * @return bool
	 */
	protected function isReconsuming(array $entry): bool
	{
		return $entry['libraryEntry']['reconsuming'];
	}
}
