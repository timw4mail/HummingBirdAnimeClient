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

use Aviat\AnimeClient\API\Mapping\AnimeWatchingStatus;
use Aviat\AnimeClient\Types\HistoryItem;
use Aviat\Ion\Di\ContainerAware;

class AnimeHistoryTransformer {
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
			if ( ! isset($entry['relationships']['anime']))
			{
				continue;
			}

			if (in_array($id, $this->skipList, FALSE))
			{
				continue;
			}

			if ($entry['attributes']['kind'] === 'progressed')
			{
				$output[] = $this->transformProgress($entry);
			}
			else if ($entry['attributes']['kind'] === 'updated')
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

				foreach ($entries as $e)
				{
					$episodes[] = max($e['original']['attributes']['changedData']['progress']);
				}
				$firstEpisode = min($episodes);
				$lastEpisode = max($episodes);

				$title = $entries[0]['title'];

				$action = (count($entries) > 3)
					? "Marathoned episodes {$firstEpisode}-{$lastEpisode} of {$title}"
					: "Watched episodes {$firstEpisode}-{$lastEpisode} of {$title}";

				$output[] = HistoryItem::check([
					'title' => $title,
					'action' => $action,
					'coverImg' => $entries[0]['coverImg'],
					'isAggregate' => true,
					'updated' => $entries[0]['updated'],
				]);

				// Skip the rest of the aggregate in the main loop
				$i += count($entries) - 1;
				continue;
			}
			else
			{
				$output[] = $entry;
			}

		}

		return $output;
	}

	protected function transformProgress ($entry): array
	{
		$animeId = array_keys($entry['relationships']['anime'])[0];
		$animeData = $entry['relationships']['anime'][$animeId]['attributes'];
		$title = $this->linkTitle($animeData);
		$imgUrl = 'images/anime/' . $animeId . '.webp';
		$episode = max($entry['attributes']['changedData']['progress']);

		return HistoryItem::check([
			'action' => "Watched episode {$episode} of {$title}",
			'coverImg' => $imgUrl,
			'kind' => 'progressed',
			'original' => $entry,
			'title' => $title,
			'updated' => $entry['attributes']['updatedAt'],
		]);
	}

	protected function transformUpdated($entry): array
	{
		$animeId = array_keys($entry['relationships']['anime'])[0];
		$animeData = $entry['relationships']['anime'][$animeId]['attributes'];
		$title = $this->linkTitle($animeData);
		$imgUrl = 'images/anime/' . $animeId . '.webp';

		$kind = array_key_first($entry['attributes']['changedData']);

		if ($kind === 'status')
		{
			$status = array_pop($entry['attributes']['changedData']['status']);
			$statusName = AnimeWatchingStatus::KITSU_TO_TITLE[$status];

			if ($statusName === 'Completed')
			{
				return HistoryItem::check([
					'action' => "Completed {$title}",
					'coverImg' => $imgUrl,
					'kind' => 'updated',
					'original' => $entry,
					'title' => $title,
					'updated' => $entry['attributes']['updatedAt'],
				]);
			}

			return HistoryItem::check([
				'action' => "Set status of {$title} to {$statusName}",
				'coverImg' => $imgUrl,
				'kind' => 'updated',
				'original' => $entry,
				'title' => $title,
				'updated' => $entry['attributes']['updatedAt'],
			]);
		}

		return $entry;
	}

	protected function linkTitle (array $animeData): string
	{
		$url = '/anime/details/' . $animeData['slug'];

		$helper = $this->getContainer()->get('html-helper');
		return $helper->a($url, $animeData['canonicalTitle'], ['id' => $animeData['slug']]);
	}
}