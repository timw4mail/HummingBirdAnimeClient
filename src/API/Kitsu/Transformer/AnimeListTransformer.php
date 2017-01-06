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
/* ?><pre><?= print_r($item, TRUE) ?></pre><?php
// die(); */
		$anime = $item['anime']['attributes'] ?? $item['anime'];
		$genres = $item['genres'] ?? [];

		$rating = (int) 2 * $item['attributes']['rating'];

		$total_episodes = array_key_exists('episodeCount', $anime)
			? (int) $anime['episodeCount']
			: '-';

		return [
			'id' => $item['id'],
			'episodes' => [
				'watched' => $item['attributes']['progress'],
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
				'titles' => Kitsu::filterTitles($anime),
				'slug' => $anime['slug'],
				'url' => $anime['url'] ?? '',
				'type' => $this->string($anime['showType'])->upperCaseFirst()->__toString(),
				'image' => $anime['posterImage']['small'],
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
     * @TODO reimplement
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