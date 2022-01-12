<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 8
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2021  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     5.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu\Transformer;

use Aviat\AnimeClient\Kitsu;
use Aviat\AnimeClient\Types\{FormItem, AnimeListItem, MangaListItem, MangaListItemDetail};
use Aviat\Ion\Transformer\AbstractTransformer;
use Aviat\Ion\Type\StringType;

/**
 * Transformer for anime list
 */
final class LibraryEntryTransformer extends AbstractTransformer
{
	public function transform(array|object $item): AnimeListItem|MangaListItem
	{
		$item = (array)$item;
		$type = $item['media']['type'] ?? '';

		$genres = [];
		if ($type !== '')
		{
			$genres = array_column($item['media']['categories']['nodes'], 'title');
			sort($genres);
		}

		return match (strtolower($type))
		{
			'anime' => $this->animeTransform($item, $genres),
			'manga' => $this->mangaTransform($item, $genres),
			default => AnimeListItem::from([]),
		};
	}

	private function animeTransform(array $item, array $genres): AnimeListItem
	{
		$animeId = $item['media']['id'];
		$anime = $item['media'];

		$rating = (int) $item['rating'] !== 0
			? $item['rating'] / 2
			: '-';

		$total_episodes = array_key_exists('episodeCount', $anime) && (int) $anime['episodeCount'] !== 0
			? (int) $anime['episodeCount']
			: '-';

		$MALid = NULL;

		if (isset($anime['mappings']['nodes']))
		{
			foreach ($anime['mappings']['nodes'] as $mapping)
			{
				if ($mapping['externalSite'] === 'MYANIMELIST_ANIME')
				{
					$MALid = $mapping['externalId'];
					break;
				}
			}
		}

		$streamingLinks = array_key_exists('nodes', $anime['streamingLinks'])
			? Kitsu::parseStreamingLinks($anime['streamingLinks']['nodes'])
			: [];

		$titles = Kitsu::getFilteredTitles($anime['titles']);
		$title = $anime['titles']['canonical'];

		return AnimeListItem::from([
			'id' => $item['id'],
			'mal_id' => $MALid,
			'episodes' => [
				'watched' => (int) $item['progress'] !== 0
					? (int) $item['progress']
					: '-',
				'total' => $total_episodes,
				'length' => $anime['episodeLength'],
			],
			'airing' => [
				'status' => Kitsu::getAiringStatus($anime['startDate'], $anime['endDate']),
				'started' => $anime['startDate'],
				'ended' => $anime['endDate']
			],
			'anime' => [
				'id' => $animeId,
				'age_rating' => $anime['ageRating'],
				'title' => $title,
				'titles' => $titles,
				'slug' => $anime['slug'],
				'show_type' => (string)StringType::from($anime['subtype'])->upperCaseFirst(),
				'cover_image' => $anime['posterImage']['views'][1]['url']
					?? $anime['posterImage']['original']['url']
					?? '/public/images/placeholder.png',
				'genres' => $genres,
				'streaming_links' => $streamingLinks,
			],
			'watching_status' => $item['status'],
			'notes' => $item['notes'],
			'rewatching' => (bool) $item['reconsuming'],
			'rewatched' => (int) $item['reconsumeCount'],
			'user_rating' => $rating,
			'private' => $item['private'] ?? FALSE,
		]);
	}

	private function mangaTransform(array $item, array $genres): MangaListItem
	{
		$mangaId = $item['media']['id'];
		$manga = $item['media'];

		$rating = (int) $item['rating'] !== 0
			? $item['rating'] / 2
			: '-';

		$totalChapters = ((int) $manga['chapterCount'] !== 0)
			? $manga['chapterCount']
			: '-';

		$totalVolumes = ((int) $manga['volumeCount'] !== 0)
			? $manga['volumeCount']
			: '-';

		$readChapters = ((int) $item['progress'] !== 0)
			? $item['progress']
			: '-';

		$MALid = NULL;

		if (isset($manga['mappings']['nodes']))
		{
			foreach ($manga['mappings']['nodes'] as $mapping)
			{
				if ($mapping['externalSite'] === 'MYANIMELIST_MANGA')
				{
					$MALid = $mapping['externalId'];
					break;
				}
			}
		}

		$titles = Kitsu::getFilteredTitles($manga['titles']);
		$title = $manga['titles']['canonical'];

		return MangaListItem::from([
			'id' => $item['id'],
			'mal_id' => $MALid,
			'chapters' => [
				'read' => $readChapters,
				'total' => $totalChapters
			],
			'volumes' => [
				'read' => '-', //$item['attributes']['volumes_read'],
				'total' => $totalVolumes
			],
			'manga' => MangaListItemDetail::from([
				'genres' => $genres,
				'id' => $mangaId,
				'image' => $manga['posterImage']['views'][1]['url'],
				'slug' => $manga['slug'],
				'title' => $title,
				'titles' => $titles,
				'type' => (string)StringType::from($manga['subtype'])->upperCaseFirst(),
				'url' => 'https://kitsu.io/manga/' . $manga['slug'],
			]),
			'reading_status' => strtolower($item['status']),
			'notes' => $item['notes'],
			'rereading' => (bool)$item['reconsuming'],
			'reread' => $item['reconsumeCount'],
			'user_rating' => $rating,
		]);
	}
}