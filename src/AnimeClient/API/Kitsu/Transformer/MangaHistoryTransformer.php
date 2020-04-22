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

use Aviat\AnimeClient\API\Mapping\MangaReadingStatus;
use Aviat\AnimeClient\Types\HistoryItem;
use Aviat\Ion\Di\ContainerAware;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

class MangaHistoryTransformer {
	use ContainerAware;

	protected array $skipList = [];

	/**
	 * Convert raw history
	 *
	 * @param array $data
	 * @return array
	 */
	public function transform(array $data): array
	{
		$output = [];

		foreach ($data as $id => $entry)
		{
			if ( ! isset($entry['relationships']['manga']))
			{
				continue;
			}

			if (in_array($id, $this->skipList, FALSE))
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
				$chapters = [];
				$updated = [];

				foreach ($entries as $e)
				{
					$chapters[] = max($e['original']['attributes']['changedData']['progress']);
					$updated[] = $e['updated'];
				}
				$firstChapter = min($chapters);
				$lastChapter = max($chapters);
				$firstUpdate = min($updated);
				$lastUpdate = max($updated);

				$title = $entries[0]['title'];

				$action = (count($entries) > 3)
					? "Marathoned chapters {$firstChapter}-{$lastChapter}"
					: "Watched chapters {$firstChapter}-{$lastChapter}";

				$output[] = HistoryItem::from([
					'action' => $action,
					'coverImg' => $entries[0]['coverImg'],
					'dateRange' => [$firstUpdate, $lastUpdate],
					'isAggregate' => true,
					'title' => $title,
					'updated' => $entries[0]['updated'],
				]);

				// Skip the rest of the aggregate in the main loop
				$i += count($entries) - 1;
				continue;
			}

			$output[] = $entry;
		}

		return $output;
	}

	protected function transformProgress ($entry): HistoryItem
	{
		$mangaId = array_keys($entry['relationships']['manga'])[0];
		$mangaData = $entry['relationships']['manga'][$mangaId]['attributes'];
		$title = $this->linkTitle($mangaData);
		$imgUrl = 'images/manga/' . $mangaId . '.webp';
		$chapter = max($entry['attributes']['changedData']['progress']);

		return HistoryItem::from([
			'action' => "Watched chapter {$chapter}",
			'coverImg' => $imgUrl,
			'kind' => 'progressed',
			'original' => $entry,
			'title' => $title,
			'updated' => $this->parseDate($entry['attributes']['updatedAt']),
		]);
	}

	protected function transformUpdated($entry): HistoryItem
	{
		$mangaId = array_keys($entry['relationships']['manga'])[0];
		$mangaData = $entry['relationships']['manga'][$mangaId]['attributes'];
		$title = $this->linkTitle($mangaData);
		$imgUrl = 'images/manga/' . $mangaId . '.webp';

		$kind = array_key_first($entry['attributes']['changedData']);

		if ($kind === 'status')
		{
			$status = array_pop($entry['attributes']['changedData']['status']);
			$statusName = MangaReadingStatus::KITSU_TO_TITLE[$status];

			if ($statusName === 'Completed')
			{
				return HistoryItem::from([
					'action' => 'Completed',
					'coverImg' => $imgUrl,
					'kind' => 'updated',
					'original' => $entry,
					'title' => $title,
					'updated' => $this->parseDate($entry['attributes']['updatedAt']),
				]);
			}

			return HistoryItem::from([
				'action' => "Set status to {$statusName}",
				'coverImg' => $imgUrl,
				'kind' => 'updated',
				'original' => $entry,
				'title' => $title,
				'updated' => $this->parseDate($entry['attributes']['updatedAt']),
			]);
		}

		return $entry;
	}

	protected function linkTitle (array $mangaData): string
	{
		$url = '/manga/details/' . $mangaData['slug'];

		$helper = $this->getContainer()->get('html-helper');
		return $helper->a($url, $mangaData['canonicalTitle'], ['id' => $mangaData['slug']]);
	}

	protected function parseDate (string $date): DateTimeImmutable
	{
		$dateTime = DateTimeImmutable::createFromFormat(
			DateTimeInterface::RFC3339_EXTENDED,
			$date
		);

		return $dateTime->setTimezone(new DateTimeZone(date_default_timezone_get()));
	}
}