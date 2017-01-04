<?php declare(strict_types=1);
/**
 * Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     AnimeListClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2016  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     4.0
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu\Transformer;

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
/* ?><pre><?= print_r($item, TRUE) ?></pre><?php
// die(); */
		$anime =& $item['anime'];
		$genres = $item['anime']['genres'] ?? [];

		$rating = (int) 2 * $item['attributes']['rating'];

		$total_episodes = array_key_exists('episodeCount', $item['anime'])
			? (int) $anime['episodeCount']
			: '-';

		$alternate_title = NULL;
		if (array_key_exists('titles', $item['anime']) && array_key_exists('en_jp', $anime['titles']))
		{
			// If the alternate title is very similar, or
			// a subset of the main title, don't list the
			// alternate title
			$not_subset = stripos($anime['canonicalTitle'], $anime['titles']['en_jp']) === FALSE;
			$diff = levenshtein($anime['canonicalTitle'], $anime['titles']['en_jp'] ?? '');
			if ($not_subset && $diff >= 5)
			{
				$alternate_title = $anime['titles']['en_jp'];
			}
		}

		return [
			'id' => $item['id'],
			'episodes' => [
				'watched' => $item['attributes']['progress'],
				'total' => $total_episodes,
				'length' => $anime['attributes']['episodeLength'],
			],
			'airing' => [
				'status' => $anime['status'] ?? '',
				'started' => $anime['attributes']['startDate'],
				'ended' => $anime['attributes']['endDate']
			],
			'anime' => [
				'age_rating' => $anime['attributes']['ageRating'],
				'title' => $anime['attributes']['canonicalTitle'],
				'alternate_title' => $alternate_title,
				'slug' => $item['relationships']['media']['data']['id'],//$anime['slug'],
				'url' => $anime['attributes']['url'] ?? '',
				'type' => $anime['attributes']['showType'],
				'image' => $anime['attributes']['posterImage']['small'],
				'genres' => $genres,
			],
			'watching_status' => $item['attributes']['status'],
			'notes' => $item['attributes']['notes'],
			'rewatching' => (bool) $item['attributes']['reconsuming'],
			'rewatched' => (int) $item['attributes']['reconsumeCount'],
			'user_rating' => ($rating === 0) ? '-' : $rating,
			'private' => (bool) $item['attributes']['private'] ?? false,
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
		// Messy mapping of boolean values to their API string equivalents
		$privacy = 'public';
		if (array_key_exists('private', $item) && $item['private'])
		{
			$privacy = 'private';
		}

		$rewatching = 'false';
		if (array_key_exists('rewatching', $item) && $item['rewatching'])
		{
			$rewatching = 'true';
		}

		return [
			'id' => $item['id'],
			'status' => $item['watching_status'],
			'sane_rating_update' => $item['user_rating'] / 2,
			'rewatching' => $rewatching,
			'rewatched_times' => $item['rewatched'],
			'notes' => $item['notes'],
			'episodes_watched' => $item['episodes_watched'],
			'privacy' => $privacy
		];
	}
}
// End of AnimeListTransformer.php