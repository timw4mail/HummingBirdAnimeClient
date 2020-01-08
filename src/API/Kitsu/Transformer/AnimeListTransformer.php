<?php declare(strict_types=1);
/**
 * Hummingbird Anime List Client
 *
 * An API client for Kitsu to manage anime and manga watch lists
 *
 * PHP version 7.2
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2020  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.2
 * @link        https://git.timshomepage.net/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu\Transformer;

use Aviat\AnimeClient\API\Kitsu;
use Aviat\AnimeClient\Types\{
	FormItem,
	AnimeListItem
};
use Aviat\Ion\Transformer\AbstractTransformer;

/**
 * Transformer for anime list
 */
final class AnimeListTransformer extends AbstractTransformer {

	/**
	 * Convert raw api response to a more
	 * logical and workable structure
	 *
	 * @param  array  $item API library item
	 * @return AnimeListItem
	 */
	public function transform($item): AnimeListItem
	{
		$included = $item['included'];
		$animeId = $item['relationships']['media']['data']['id'];
		$anime = $included['anime'][$animeId];

		$genres = [];

		foreach($anime['relationships']['categories'] as $genre)
		{
			$genres[] = $genre['title'];
		}

		sort($genres);

		$rating = (int) $item['attributes']['ratingTwenty'] !== 0
			? $item['attributes']['ratingTwenty'] / 2
			: '-';

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

		$streamingLinks = array_key_exists('streamingLinks', $anime['relationships'])
			? Kitsu::parseListItemStreamingLinks($included, $animeId)
			: [];

		$titles = Kitsu::filterTitles($anime);
		$title = array_shift($titles);

		return new AnimeListItem([
			'id' => $item['id'],
			'mal_id' => $MALid,
			'episodes' => [
				'watched' => (int) $item['attributes']['progress'] !== 0
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
				'id' => $animeId,
				'age_rating' => $anime['ageRating'],
				'title' => $title,
				'titles' => $titles,
				'slug' => $anime['slug'],
				'show_type' => (string)$this->string($anime['subtype'])->upperCaseFirst(),
				'cover_image' => $anime['posterImage']['small'],
				'genres' => $genres,
				'streaming_links' => $streamingLinks,
			],
			'watching_status' => $item['attributes']['status'],
			'notes' => $item['attributes']['notes'],
			'rewatching' => (bool) $item['attributes']['reconsuming'],
			'rewatched' => (int) $item['attributes']['reconsumeCount'],
			'user_rating' => $rating,
			'private' => $item['attributes']['private'] ?? FALSE,
		]);
	}

	/**
	 * Convert transformed data to
	 * api response format
	 *
	 * @param array $item Transformed library item
	 * @return FormItem API library item
	 */
	public function untransform($item): FormItem
	{
		$privacy = (array_key_exists('private', $item) && $item['private']);
		$rewatching = (array_key_exists('rewatching', $item) && $item['rewatching']);

		$untransformed = new FormItem([
			'id' => $item['id'],
			'anilist_item_id' => $item['anilist_item_id'] ?? NULL,
			'mal_id' => $item['mal_id'] ?? NULL,
			'data' => [
				'status' => $item['watching_status'],
				'reconsuming' => $rewatching,
				'reconsumeCount' => $item['rewatched'],
				'notes' => $item['notes'],
				'private' => $privacy
			]
		]);

		if (is_numeric($item['episodes_watched']) && $item['episodes_watched'] > 0)
		{
			$untransformed['data']['progress'] = (int) $item['episodes_watched'];
		}

		if (is_numeric($item['user_rating']) && $item['user_rating'] > 0)
		{
			$untransformed['data']['ratingTwenty'] = $item['user_rating'] * 2;
		}

		return $untransformed;
	}
}
// End of AnimeListTransformer.php