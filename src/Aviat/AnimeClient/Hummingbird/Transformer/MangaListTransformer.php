<?php
/**
 * Hummingbird Anime Client
 *
 * An API client for Hummingbird to manage anime and manga watch lists
 *
 * @package     HummingbirdAnimeClient
 * @author      Timothy J. Warren
 * @copyright   Copyright (c) 2015 - 2016
 * @link        https://github.com/timw4mail/HummingBirdAnimeClient
 * @license     MIT
 */

namespace Aviat\AnimeClient\Hummingbird\Transformer;

use Aviat\Ion\Transformer\AbstractTransformer;

/**
 * Data transformation class for zippered Hummingbird manga
 */
class MangaListTransformer extends AbstractTransformer {

	use \Aviat\Ion\StringWrapper;

	/**
	 * Remap zipped anime data to a more logical form
	 *
	 * @param  array  $item manga entry item
	 * @return array
	 */
	public function transform($item)
	{
		$manga =& $item['manga'];

		$rating = (is_numeric($item['rating']))
			? intval(2 * $item['rating'])
			: '-';

		$total_chapters = ($manga['chapter_count'] > 0)
			? $manga['chapter_count']
			: '-';

		$total_volumes = ($manga['volume_count'] > 0)
			? $manga['volume_count']
			: '-';

		$map = [
			'id' => $item['id'],
			'chapters' => [
				'read' => $item['chapters_read'],
				'total' => $total_chapters
			],
			'volumes' => [
				'read' => $item['volumes_read'],
				'total' => $total_volumes
			],
			'manga' => [
				'title' => $manga['romaji_title'],
				'alternate_title' => NULL,
				'slug' => $manga['id'],
				'url' => 'https://hummingbird.me/manga/' . $manga['id'],
				'type' => $manga['manga_type'],
				'image' => $manga['poster_image_thumb'],
				'genres' => $manga['genres'],
			],
			'reading_status' => $item['status'],
			'notes' => $item['notes'],
			'rereading' => (bool)$item['rereading'],
			'reread' => $item['reread_count'],
			'user_rating' => $rating,
		];

		if (array_key_exists('english_title', $manga))
		{
			$diff = levenshtein($manga['romaji_title'], $manga['english_title']);

			// If the titles are REALLY similar, don't bother showing both
			if ($diff >= 5)
			{
				$map['manga']['alternate_title'] = $manga['english_title'];
			}
		}

		return $map;
	}

	/**
	 * Untransform data to update the api
	 *
	 * @param  array $item
	 * @return array
	 */
	public function untransform($item)
	{
		$rereading = (array_key_exists('rereading', $item)) && (bool)$item['rereading'];

		$map = [
			'id' => $item['id'],
			'manga_id' => $item['manga_id'],
			'status' => $item['status'],
			'chapters_read' => (int)$item['chapters_read'],
			'volumes_read' => (int)$item['volumes_read'],
			'rereading' => $rereading,
			'reread_count' => (int)$item['reread_count'],
			'notes' => $item['notes'],
		];

		if ($item['new_rating'] !== $item['old_rating'] && ! empty($item['new_rating']))
		{
			$map['rating'] = ($item['new_rating'] > 0)
				? $item['new_rating'] / 2
				: $item['old_rating'] / 2;
		}

		return $map;
	}
}
// End of MangaListTransformer.php