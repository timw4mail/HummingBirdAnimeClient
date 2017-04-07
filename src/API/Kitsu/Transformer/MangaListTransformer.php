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
		$included = $item['included'];
		$mangaId = $item['relationships']['media']['data']['id'];
		$manga = $included['manga'][$mangaId];

		$genres = array_column($manga['relationships']['genres'], 'name') ?? [];
		sort($genres);

		$rating = (int) $item['attributes']['rating'] !== 0
			? (int) 2 * $item['attributes']['rating']
			: '-';

		$totalChapters = ((int) $manga['chapterCount'] !== 0)
			? $manga['chapterCount']
			: '-';

		$totalVolumes = ((int) $manga['volumeCount'] !== 0)
			? $manga['volumeCount']
			: '-';

		$readChapters = ((int) $item['attributes']['progress'] !== 0)
			? $item['attributes']['progress']
			: '-';

		$MALid = NULL;

		if (array_key_exists('mappings', $manga['relationships']))
		{
			foreach ($manga['relationships']['mappings'] as $mapping)
			{
				if ($mapping['externalSite'] === 'myanimelist/manga')
				{
					$MALid = $mapping['externalId'];
					break;
				}
			}
		}

		$map = [
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
			'manga' => [
				'titles' => Kitsu::filterTitles($manga),
				'alternate_title' => NULL,
				'slug' => $manga['slug'],
				'url' => 'https://kitsu.io/manga/' . $manga['slug'],
				'type' => $manga['mangaType'],
				'image' => $manga['posterImage']['small'],
				'genres' => $genres,
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
			'mal_id' => $item['mal_id'],
			'data' => [
				'status' => $item['status'],
				'progress' => (int)$item['chapters_read'],
				'reconsuming' => $rereading,
				'reconsumeCount' => (int)$item['reread_count'],
				'notes' => $item['notes'],
			],
		];

		if (is_numeric($item['new_rating']))
		{
			$map['data']['rating'] = $item['new_rating'] / 2;
		}

		return $map;
	}
}
// End of MangaListTransformer.php