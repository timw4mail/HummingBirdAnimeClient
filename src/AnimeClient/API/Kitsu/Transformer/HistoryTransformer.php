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
				$episodes = [];
				$updated = [];

				foreach ($entries as $e)
				{
					$episodes[] = max($e['original']['attributes']['changedData']['progress']);
					$updated[] = $e['updated'];
				}
				$firstEpisode = min($episodes);
				$lastEpisode = max($episodes);
				$firstUpdate = min($updated);
				$lastUpdate = max($updated);

				$title = $entries[0]['title'];

				$action = (count($entries) > 3)
					? "Marathoned episodes {$firstEpisode}-{$lastEpisode}"
					: "Watched episodes {$firstEpisode}-{$lastEpisode}";

				$output[] = HistoryItem::from([
					'action' => $action,
					'coverImg' => $entries[0]['coverImg'],
					'dateRange' => [$firstUpdate, $lastUpdate],
					'isAggregate' => true,
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
		$episode = max($entry['attributes']['changedData']['progress']);

		$action = ($this->type === 'anime')
			? "Watched episode {$episode}"
			: "Read chapter {$episode}";

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
}