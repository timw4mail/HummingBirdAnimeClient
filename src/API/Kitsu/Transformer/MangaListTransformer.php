<?php declare(strict_types=1);
/**
 * Anime List Client
 *
 * An API client for Kitsu and MyAnimeList to manage anime and manga watch lists
 *
 * PHP version 7
 *
 * @package	 AnimeListClient
 * @author	  Timothy J. Warren <tim@timshomepage.net>
 * @copyright   2015 - 2016  Timothy J. Warren
 * @license	 http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version	 4.0
 * @link		https://github.com/timw4mail/HummingBirdAnimeClient
 */

namespace Aviat\AnimeClient\API\Kitsu\Transformer;

use Aviat\AnimeClient\API\Kitsu;
use Aviat\Ion\StringWrapper;
use Aviat\Ion\Transformer\AbstractTransformer;

/**
 * Data transformation class for zippered Hummingbird manga
 */
class MangaListTransformer extends AbstractTransformer {

	use StringWrapper;

	/**
	 * Remap zipped anime data to a more logical form
	 *
	 * @param  array  $item manga entry item
	 * @return array
	 */
	public function transform($item)
	{
/*?><pre><?= print_r($item, TRUE) ?></pre><?php*/
		$manga =& $item['manga'];

		$rating = (is_numeric($item['attributes']['rating']))
			? intval(2 * $item['attributes']['rating'])
			: '-';

		$total_chapters = ($manga['attributes']['chapterCount'] > 0)
			? $manga['attributes']['chapterCount']
			: '-';

		$total_volumes = ($manga['attributes']['volumeCount'] > 0)
			? $manga['attributes']['volumeCount']
			: '-';

		$map = [
			'id' => $item['id'],
			'chapters' => [
				'read' => $item['attributes']['progress'],
				'total' => $total_chapters
			],
			'volumes' => [
				'read' => '-', //$item['attributes']['volumes_read'],
				'total' => $total_volumes
			],
			'manga' => [
				'titles' => Kitsu::filterTitles($manga['attributes']),
				'alternate_title' => NULL,
				'slug' => $manga['attributes']['slug'],
				'url' => 'https://kitsu.io/manga/' . $manga['attributes']['slug'],
				'type' => $manga['attributes']['mangaType'],
				'image' => $manga['attributes']['posterImage']['small'],
				'genres' => [], //$manga['genres'],
			],
			'reading_status' => $item['attributes']['status'],
			'notes' => $item['attributes']['notes'],
			'rereading' => (bool)$item['attributes']['reconsuming'],
			'reread' => $item['attributes']['reconsumeCount'],
			'user_rating' => $rating,
		];

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

		if ($item['new_rating'] !== $item['old_rating'] && $item['new_rating'] !== "")
		{
			$map['rating'] = ($item['new_rating'] > 0)
				? $item['new_rating'] / 2
				: $item['old_rating'] / 2;
		}

		return $map;
	}
}
// End of MangaListTransformer.php