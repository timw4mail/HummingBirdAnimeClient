<?php declare(strict_types=1);
/**
 * Hummingbird Anime Client
 *
 * An API client for Hummingbird to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2016  Timothy J. Warren
 * @license     http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version     3.1
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\Hummingbird\Transformer;

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
		$anime =& $item['anime'];
		$genres = $this->linearizeGenres($item['anime']['genres']);

		$rating = NULL;
		if ($item['rating']['type'] === 'advanced')
		{
			$rating = is_numeric($item['rating']['value'])
				? (int) 2 * $item['rating']['value']
				: '-';
		}

		$total_episodes = is_numeric($anime['episode_count'])
			? $anime['episode_count']
			: '-';

		$alternate_title = NULL;
		if (array_key_exists('alternate_title', $anime))
		{
			// If the alternate title is very similar, or
			// a subset of the main title, don't list the
			// alternate title
			$not_subset = stripos($anime['title'], $anime['alternate_title']) === FALSE;
			$diff = levenshtein($anime['title'], $anime['alternate_title'] ?? '');
			if ($not_subset && $diff >= 5)
			{
				$alternate_title = $anime['alternate_title'];
			}
		}

		return [
			'id' => $item['id'],
			'episodes' => [
				'watched' => $item['episodes_watched'],
				'total' => $total_episodes,
				'length' => $anime['episode_length'],
			],
			'airing' => [
				'status' => $anime['status'],
				'started' => $anime['started_airing'],
				'ended' => $anime['finished_airing']
			],
			'anime' => [
				'age_rating' => $anime['age_rating'],
				'title' => $anime['title'],
				'alternate_title' => $alternate_title,
				'slug' => $anime['slug'],
				'url' => $anime['url'],
				'type' => $anime['show_type'],
				'image' => $anime['cover_image'],
				'genres' => $genres,
			],
			'watching_status' => $item['status'],
			'notes' => $item['notes'],
			'rewatching' => (bool) $item['rewatching'],
			'rewatched' => $item['rewatched_times'],
			'user_rating' => $rating,
			'private' => (bool) $item['private'],
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

	/**
	 * Simplify structure of genre list
	 *
	 * @param  array  $rawGenres
	 * @return array
	 */
	protected function linearizeGenres(array $rawGenres): array
	{
		$genres = [];

		foreach ($rawGenres as $genre)
		{
			$genres[] = $genre['name'];
		}

		return $genres;
	}
}
// End of AnimeListTransformer.php