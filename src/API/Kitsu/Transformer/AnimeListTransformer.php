<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2017  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu\Transformer;

use Aviat\AnimeClient\API\Kitsu;
use Aviat\Ion\Transformer\AbstractTransformer;

/**
 * Transformer for anime list
 */
class AnimeListTransformer extends AbstractTransformer {

	/**
	 * Convert raw api response to a more
	 * logical and workable structure
	 *
	 * @param  array  $item API library item
	 * @return array
	 */
	public function transform($item)
	{
		$included = $item['included'];
		$animeId = $item['relationships']['media']['data']['id'];
		$anime = $included['anime'][$animeId];

		$genres = array_column($anime['relationships']['genres'], 'name') ?? [];
		sort($genres);

		$rating = (int) 2 * $item['attributes']['rating'];

		$total_episodes = array_key_exists('episodeCount', $anime) && (int) $anime['episodeCount'] !== 0
			? (int) $anime['episodeCount']
			: '-';

		$MALid = NULL;

		if (array_key_exists('mappings', $anime['relationships']))
		{
			foreach ($anime['relationships']['mappings'] as $mapping)
			{
				if ($mapping['externalSite'] === 'myanimelist/anime')
				{
					$MALid = $mapping['externalId'];
					break;
				}
			}
		}

		$streamingLinks = (array_key_exists('streamingLinks', $anime['relationships']))
			? Kitsu::parseListItemStreamingLinks($included, $animeId)
			: [];

		return [
			'id' => $item['id'],
			'mal_id' => $MALid,
			'episodes' => [
				'watched' => (int) $item['attributes']['progress'] !== '0'
					? (int) $item['attributes']['progress']
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
				'age_rating' => $anime['ageRating'],
				'title' => $anime['canonicalTitle'],
				'titles' => Kitsu::filterTitles($anime),
				'slug' => $anime['slug'],
				'type' => $this->string($anime['showType'])->upperCaseFirst()->__toString(),
				'image' => $anime['posterImage']['small'],
				'genres' => $genres,
				'streaming_links' => $streamingLinks,
			],
			'watching_status' => $item['attributes']['status'],
			'notes' => $item['attributes']['notes'],
			'rewatching' => (bool) $item['attributes']['reconsuming'],
			'rewatched' => (int) $item['attributes']['reconsumeCount'],
			'user_rating' => ($rating === 0) ? '-' : (int) $rating,
			'private' => (bool) $item['attributes']['private'] ?? FALSE,
		];
	}

	/**
	 * Convert transformed data to
	 * api response format
	 *
	 * @param array $item Transformed library item
	 * @return array API library item
	 */
	public function untransform($item)
	{
		$privacy = (array_key_exists('private', $item) && $item['private']);
		$rewatching = (array_key_exists('rewatching', $item) && $item['rewatching']);

		$untransformed = [
			'id' => $item['id'],
			'mal_id' => $item['mal_id'] ?? NULL,
			'data' => [
				'status' => $item['watching_status'],
				'reconsuming' => $rewatching,
				'reconsumeCount' => $item['rewatched'],
				'notes' => $item['notes'],
				'progress' => $item['episodes_watched'],
				'private' => $privacy
			]
		];

		if (is_numeric($item['user_rating']) && $item['user_rating'] > 0)
		{
			$untransformed['data']['rating'] = $item['user_rating'] / 2;
		}

		return $untransformed;
	}
}
// End of AnimeListTransformer.php