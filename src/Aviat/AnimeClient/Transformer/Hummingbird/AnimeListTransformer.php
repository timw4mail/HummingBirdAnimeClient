<?php

namespace Aviat\AnimeClient\Transformer\Hummingbird;

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
		$genres = $this->linearize_genres($item['anime']['genres']);

		$rating = NULL;
		if ($item['rating']['type'] === 'advanced')
		{
			$rating = (is_numeric($item['rating']['value']))
				? intval(2 * $item['rating']['value'])
				: '-';
		}

		$total_episodes = (is_numeric($anime['episode_count']))
			? $anime['episode_count']
			: '-';

		$alternate_title = NULL;
		if (array_key_exists('alternate_title', $anime))
		{
			// If the alternate title is very similar, or
			// a subset of the main title, don't list the
			// alternate title
			$not_subset = stripos($anime['title'], $anime['alternate_title']) === FALSE;
			$diff = levenshtein($anime['title'], $anime['alternate_title']);
			if ($not_subset && $diff >= 5)
			{
				$alternate_title = $anime['alternate_title'];
			}
		}

		return [
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
			'id' => $item['id'],
			'watching_status' => $item['status'],
			'notes' => $item['notes'],
			'rewatching' => (bool) $item['rewatching'],
			'rewatched' => $item['rewatched_times'],
			'user_rating' => $rating,
		];
	}

	/**
	 * Simplify structure of genre list
	 *
	 * @param  array  $raw_genres
	 * @return array
	 */
	protected function linearize_genres(array $raw_genres)
	{
		$genres = [];

		foreach($raw_genres as $genre)
		{
			$genres[] = $genre['name'];
		}

		return $genres;
	}
}
// End of AnimeListTransformer.php