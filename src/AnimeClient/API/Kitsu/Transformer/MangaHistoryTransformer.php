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

class MangaHistoryTransformer extends HistoryTransformer {
	protected string $type = 'manga';

	protected array $statusMap = MangaReadingStatus::KITSU_TO_TITLE;

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
}