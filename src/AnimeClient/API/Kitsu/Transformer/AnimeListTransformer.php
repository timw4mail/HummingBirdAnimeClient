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
use Aviat\AnimeClient\Types\{
	FormItem,
	AnimeListItem
};
use Aviat\Ion\Transformer\AbstractTransformer;
use Aviat\Ion\Type\StringType;

/**
 * Transformer for anime list
 */
final class AnimeListTransformer extends AbstractTransformer {

	/**
	 * Convert raw api response to a more
	 * logical and workable structure
	 *
	 * @param  array|object  $item API library item
	 * @return AnimeListItem
	 */
	public function transform(array|object $item): AnimeListItem
	{
		$item = (array)$item;
		$animeId = $item['media']['id'];
		$anime = $item['media'];

		$genres = [];

		$rating = (int) $item['rating'] !== 0
			? (int)$item['rating'] / 2
			: '-';

		$total_episodes = (int) $anime['episodeCount'] !== 0
			? (int) $anime['episodeCount']
			: '-';

		$MALid = NULL;

		$mappings = $anime['mappings']['nodes'] ?? [];
		if ( ! empty($mappings))
		{
			foreach ($mappings as $mapping)
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
				'cover_image' => Kitsu::getPosterImage($anime),
				'genres' => $genres,
				'streaming_links' => $streamingLinks,
			],
			'watching_status' => $item['status'],
			'notes' => $item['notes'],
			'rewatching' => (bool) $item['reconsuming'],
			'rewatched' => (int) $item['reconsumeCount'],
			'user_rating' => (is_string($rating)) ? $rating : (int) $rating,
			'private' => $item['private'] ?? FALSE,
		]);
	}

	/**
	 * Convert transformed data to
	 * api response format
	 *
	 * @param array $item Transformed library item
	 * @return FormItem API library item
	 */
	public function untransform(array $item): FormItem
	{
		$privacy = (array_key_exists('private', $item) && $item['private']);
		$rewatching = (array_key_exists('rewatching', $item) && $item['rewatching']);

		$untransformed = FormItem::from([
			'id' => $item['id'],
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